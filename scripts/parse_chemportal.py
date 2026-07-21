#!/usr/bin/env python3
"""Parse locally downloaded Chemportal catalog pages into auditable JSON/CSV."""

from __future__ import annotations

import argparse
import csv
import json
import re
from pathlib import Path
from urllib.parse import parse_qs, urlparse

from lxml import html


INTERMEDIARY_DISCLOSURE = (
    "SmartKeyTurkey acts as an authorized sales representative and sourcing coordinator; "
    "it is not the manufacturer. Current specifications, availability, pricing and final "
    "commercial terms are confirmed for each inquiry."
)


def clean_text(value: str) -> str:
    return re.sub(r"\s+", " ", value or "").strip().strip('"“”')


def slug_from_url(url: str) -> str:
    return parse_qs(urlparse(url).query).get("product", [""])[0]


def generated_description(row: dict) -> str:
    applications = [clean_text(item) for item in row.get("applications", []) if clean_text(item)]
    if applications:
        use_text = "; ".join(applications[:2])
        return (
            f"{row['product_name']} is a {row['product_family']} grade intended for applications "
            f"including {use_text}. Review the listed technical properties and confirm current "
            "specifications, regulatory suitability and availability with the relevant supplier before purchase."
        )
    return (
        f"{row['product_name']} is listed in the {row['product_family']} product family. "
        "Review the technical property table and confirm the intended application, current "
        "specifications, regulatory suitability and availability with the relevant supplier before purchase."
    )


def full_image_url(img) -> str:
    if img is None:
        return ""
    srcset = img.get("srcset", "")
    candidates = [part.strip().split(" ")[0] for part in srcset.split(",") if part.strip()]
    return candidates[-1] if candidates else img.get("src", "")


def parse_listing(path: Path) -> list[dict]:
    document = html.fromstring(path.read_text(encoding="utf-8"))
    rows = []
    for card in document.xpath("//div[contains(concat(' ', normalize-space(@class), ' '), ' product-small ') and contains(concat(' ', normalize-space(@class), ' '), ' product ')]"):
        links = card.xpath(".//p[contains(@class,'product-title')]//a[contains(@href,'?product=')]")
        if not links:
            continue
        link = links[0]
        categories = card.xpath(".//p[contains(@class,'product-cat')]")
        excerpts = card.xpath(".//p[contains(@class,'box-excerpt')]")
        images = card.xpath(".//div[contains(@class,'box-image')]//img")
        category = clean_text(categories[0].text_content() if categories else "")
        excerpt = clean_text(excerpts[0].text_content() if excerpts else "")
        image = full_image_url(images[0] if images else None)
        source_url = link.get("href", "")
        title = clean_text(link.text_content())
        rows.append(
            {
                "product_name": title,
                "slug": slug_from_url(source_url),
                "product_family": category,
                "grade": title,
                "source_excerpt": excerpt,
                "source_url": source_url,
                "source_image_url": image,
                "source_listing_file": path.name,
            }
        )
    return rows


def parse_product(path: Path) -> dict:
    document = html.fromstring(path.read_text(encoding="utf-8"))
    mains = document.xpath("//main")
    main = mains[0] if mains else document
    headings = main.xpath(".//h1[contains(@class,'product-title')]") or main.xpath(".//h1")
    title = clean_text(headings[0].text_content() if headings else "")
    panels = main.xpath(".//*[@id='tab-description']")
    description_panel = panels[0] if panels else None
    description = ""
    applications: list[str] = []
    properties: list[dict] = []
    if description_panel is not None:
        paragraphs = [clean_text(p.text_content()) for p in description_panel.xpath(".//p")]
        description = next((p for p in paragraphs if p), "")
        applications = [clean_text(li.text_content()) for li in description_panel.xpath(".//li")]
        for table in description_panel.xpath(".//table"):
            rows = []
            for tr in table.xpath(".//tr"):
                cells = [clean_text(c.text_content()) for c in tr.xpath("./th|./td")]
                if cells:
                    rows.append(cells)
            if rows:
                headers = rows[0]
                for values in rows[1:]:
                    properties.append({headers[i] if i < len(headers) else f"column_{i+1}": value for i, value in enumerate(values)})
    categories = [clean_text(a.text_content()) for a in main.xpath(".//nav[contains(@class,'woocommerce-breadcrumb')]//a")][1:]
    image_links = main.xpath(".//*[contains(@class,'woocommerce-product-gallery__image')]//a") or main.xpath(".//a[contains(@href,'/wp-content/uploads/')]")
    image_link = image_links[0] if image_links else None
    source_url = ""
    canonicals = document.xpath("//link[@rel='canonical']")
    if canonicals:
        source_url = canonicals[0].get("href", "")
    return {
        "product_name": title,
        "source_url": source_url,
        "description": description,
        "applications": applications,
        "properties": properties,
        "category_path": categories,
        "source_image_url": image_link.get("href", "") if image_link is not None else "",
    }


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--listings", type=Path, required=True)
    parser.add_argument("--products", type=Path)
    parser.add_argument("--json", type=Path, required=True)
    parser.add_argument("--csv", type=Path, required=True)
    args = parser.parse_args()

    products: dict[str, dict] = {}
    for path in sorted(args.listings.glob("*.html")):
        for row in parse_listing(path):
            products[row["source_url"]] = row

    if args.products and args.products.exists():
        products_by_slug = {row["slug"]: row for row in products.values()}
        for path in sorted(args.products.glob("*.html")):
            detail = parse_product(path)
            target = products.get(detail["source_url"]) or products_by_slug.get(path.stem)
            if target is not None:
                detail["source_url"] = target["source_url"]
                target.update(detail)

    ordered = []
    for source_row in sorted(products.values(), key=lambda row: (row["product_family"], row["product_name"])):
        row = {key: value for key, value in source_row.items() if key not in {"source_excerpt", "description"}}
        row["short_description"] = generated_description(source_row)
        ordered.append(row)
    args.json.parent.mkdir(parents=True, exist_ok=True)
    args.json.write_text(json.dumps(ordered, ensure_ascii=False, indent=2), encoding="utf-8")

    fields = [
        "product_name", "slug", "product_family", "grade", "short_description",
        "physical_form", "applications", "industries", "manufacturer_supplier",
        "country_of_origin", "cas_number", "hs_gtip_code", "un_number",
        "dangerous_goods_class", "minimum_order_quantity", "quantity_unit",
        "packaging_options", "supported_incoterms", "dispatch_location",
        "availability_status", "availability_checked_date", "tds_url",
        "tds_revision_date", "sds_url", "sds_revision_date", "coa_available",
        "certificate_of_origin_available", "source_url", "source_image_url",
        "image_rights_status", "technical_properties_json", "last_reviewed_date",
        "verification_status", "rfq_enabled", "intermediary_disclosure",
    ]
    args.csv.parent.mkdir(parents=True, exist_ok=True)
    with args.csv.open("w", newline="", encoding="utf-8") as handle:
        writer = csv.DictWriter(handle, fieldnames=fields, lineterminator="\n")
        writer.writeheader()
        for row in ordered:
            writer.writerow(
                {
                    "product_name": row["product_name"],
                    "slug": row["slug"],
                    "product_family": row["product_family"],
                    "grade": row["grade"],
                    "short_description": row["short_description"],
                    "applications": " | ".join(row.get("applications", [])),
                    "availability_status": "Confirm by RFQ",
                    "coa_available": "Confirm by RFQ",
                    "certificate_of_origin_available": "Confirm by RFQ",
                    "source_url": row["source_url"],
                    "source_image_url": row.get("source_image_url", ""),
                    "image_rights_status": "Authorized for SmartKey publication — owner confirmed 2026-07-21",
                    "technical_properties_json": json.dumps(row.get("properties", []), ensure_ascii=False),
                    "last_reviewed_date": "2026-07-21",
                    "verification_status": "Source captured; technical review pending",
                    "rfq_enabled": "Yes",
                    "intermediary_disclosure": INTERMEDIARY_DISCLOSURE,
                }
            )

    print(json.dumps({"products": len(ordered), "json": str(args.json), "csv": str(args.csv)}))


if __name__ == "__main__":
    main()
