import fs from "node:fs/promises";
import path from "node:path";
import { SpreadsheetFile, Workbook } from "@oai/artifact-tool";

const [sourcePath, outputPath, previewDir] = process.argv.slice(2);
const products = JSON.parse(await fs.readFile(sourcePath, "utf8"));

const workbook = Workbook.create();
const overview = workbook.worksheets.add("Overview");
const catalog = workbook.worksheets.add("Products");
const technical = workbook.worksheets.add("Technical Properties");
const guide = workbook.worksheets.add("Field Guide");

const green = "#84C341";
const dark = "#243126";
const light = "#EEF6E8";
const gray = "#667085";

for (const sheet of [overview, catalog, technical, guide]) {
  sheet.showGridLines = false;
}

const categories = [...new Set(products.map((p) => p.product_family))].sort();
const totalProperties = products.reduce((sum, p) => sum + (p.properties?.length || 0), 0);

overview.getRange("A1:H2").merge();
overview.getRange("A1").values = [["SmartKeyTurkey — Petrochemical Product Catalog"]];
overview.getRange("A1:H2").format = {
  fill: dark,
  font: { bold: true, color: "#FFFFFF", size: 20 },
  verticalAlignment: "center",
};
overview.getRange("A4:B7").values = [
  ["Catalog status", "Source captured — technical review pending"],
  ["Products", products.length],
  ["Categories", categories.length],
  ["Technical property rows", totalProperties],
];
overview.getRange("A4:A7").format = { fill: light, font: { bold: true, color: dark } };
overview.getRange("A4:B7").format.borders = { preset: "outside", style: "thin", color: "#C9D6C2" };
overview.getRange("A9:C9").values = [["Category", "Products", "Share"]];
overview.getRange("A9:C9").format = { fill: green, font: { bold: true, color: "#FFFFFF" } };
overview.getRange(`A10:A${9 + categories.length}`).values = categories.map((c) => [c]);
for (let i = 0; i < categories.length; i += 1) {
  const row = 10 + i;
  overview.getRange(`B${row}`).formulas = [[`=COUNTIF('Products'!$D$2:$D$${products.length + 1},A${row})`]];
  overview.getRange(`C${row}`).formulas = [[`=B${row}/$B$5`]];
}
overview.getRange(`C10:C${9 + categories.length}`).format.numberFormat = "0.0%";
overview.getRange("E4:H4").merge();
overview.getRange("E4").values = [["Editorial and rights controls"]];
overview.getRange("E4:H4").format = { fill: green, font: { bold: true, color: "#FFFFFF" } };
overview.getRange("E5:H9").merge();
overview.getRange("E5").values = [[
  "Descriptions are original SmartKey summaries generated from product names, categories and applications. Technical values remain linked to their source page. The owner confirmed authorization to republish the source catalog and images on 2026-07-21. SmartKey is an authorized sales representative, not the manufacturer; current specifications and commercial terms remain subject to inquiry confirmation."
]];
overview.getRange("E5:H9").format = { fill: light, wrapText: true, verticalAlignment: "top", font: { color: dark } };
overview.getRange("A25:H26").merge();
overview.getRange("A25").values = [["Primary source: https://chemportal.com.tr/?page_id=28 — captured 2026-07-21"]];
overview.getRange("A25:H26").format = { font: { color: gray, italic: true }, wrapText: true };
overview.getRange("A:H").format.columnWidth = 16;
overview.getRange("A:A").format.columnWidth = 24;
overview.getRange("B:B").format.columnWidth = 22;
overview.getRange("E:H").format.columnWidth = 18;

const catalogHeaders = [
  "Product Name", "Slug", "Grade", "Category", "SmartKey Description", "Applications",
  "Image Source URL", "Image Rights", "Source URL", "Properties", "Verification Status",
  "Availability", "RFQ Enabled", "Last Reviewed", "Intermediary Disclosure",
];
const catalogRows = products.map((p) => [
  p.product_name,
  p.slug,
  p.grade,
  p.product_family,
  p.short_description,
  (p.applications || []).join(" | "),
  p.source_image_url || "",
  "Authorized — owner confirmed 2026-07-21",
  p.source_url,
  (p.properties || []).length,
  "Source captured; technical review pending",
  "Confirm by RFQ",
  "Yes",
  new Date("2026-07-21T00:00:00Z"),
  "SmartKeyTurkey acts as an authorized sales representative and sourcing coordinator; it is not the manufacturer. Current specifications, availability, pricing and final commercial terms are confirmed for each inquiry.",
]);
catalog.getRange(`A1:O${catalogRows.length + 1}`).values = [catalogHeaders, ...catalogRows];
catalog.getRange("A1:O1").format = { fill: dark, font: { bold: true, color: "#FFFFFF" }, wrapText: true };
catalog.getRange(`A2:O${catalogRows.length + 1}`).format = { verticalAlignment: "top" };
catalog.getRange(`D2:D${catalogRows.length + 1}`).conditionalFormats.add("containsText", { text: "HDPE", format: { fill: "#E8F4DD" } });
catalog.getRange(`N2:N${catalogRows.length + 1}`).format.numberFormat = "yyyy-mm-dd";
catalog.tables.add(`A1:O${catalogRows.length + 1}`, true, "ProductsTable");
catalog.freezePanes.freezeRows(1);
catalog.freezePanes.freezeColumns(2);
catalog.getRange("A:O").format.columnWidth = 15;
catalog.getRange("A:A").format.columnWidth = 22;
catalog.getRange("B:D").format.columnWidth = 16;
catalog.getRange("E:E").format.columnWidth = 56;
catalog.getRange("F:F").format.columnWidth = 42;
catalog.getRange("G:I").format.columnWidth = 36;
catalog.getRange("K:O").format.columnWidth = 24;
catalog.getRange(`E2:O${catalogRows.length + 1}`).format.wrapText = true;

function findField(record, candidates) {
  const key = Object.keys(record).find((k) => candidates.some((c) => k.toLowerCase().includes(c)));
  return key ? record[key] : "";
}

const technicalHeaders = ["Product", "Category", "Sequence", "Property", "Unit", "Test Method", "Value", "Additional Values", "Source URL"];
const technicalRows = [];
for (const product of products) {
  for (let i = 0; i < (product.properties || []).length; i += 1) {
    const prop = product.properties[i];
    const used = new Set();
    const take = (candidates) => {
      const key = Object.keys(prop).find((k) => candidates.some((c) => k.toLowerCase().includes(c)));
      if (key) used.add(key);
      return key ? prop[key] : "";
    };
    const property = take(["property", "characteristic", "analysis", "parameter"]);
    const unit = take(["unit"]);
    const method = take(["test method", "method"]);
    const value = take(["value", "typical"]);
    take(["no.", "no", "#"]);
    const additional = Object.fromEntries(Object.entries(prop).filter(([key]) => !used.has(key)));
    technicalRows.push([
      product.product_name,
      product.product_family,
      i + 1,
      property || Object.values(prop)[1] || Object.values(prop)[0] || "",
      unit,
      method,
      value,
      Object.keys(additional).length ? JSON.stringify(additional) : "",
      product.source_url,
    ]);
  }
}
technical.getRange(`A1:I${technicalRows.length + 1}`).values = [technicalHeaders, ...technicalRows];
technical.getRange("A1:I1").format = { fill: dark, font: { bold: true, color: "#FFFFFF" }, wrapText: true };
technical.tables.add(`A1:I${technicalRows.length + 1}`, true, "TechnicalPropertiesTable");
technical.freezePanes.freezeRows(1);
technical.freezePanes.freezeColumns(2);
technical.getRange("A:I").format.columnWidth = 16;
technical.getRange("A:B").format.columnWidth = 22;
technical.getRange("D:D").format.columnWidth = 34;
technical.getRange("F:F").format.columnWidth = 22;
technical.getRange("G:H").format.columnWidth = 30;
technical.getRange("I:I").format.columnWidth = 42;
technical.getRange(`D2:I${technicalRows.length + 1}`).format.wrapText = true;

const guideRows = [
  ["Field", "Meaning / publication rule"],
  ["SmartKey Description", "Original SmartKey wording generated from factual category/application data; human technical review required."],
  ["Technical Properties", "Values transcribed from the named source page. Confirm against current supplier TDS before use."],
  ["Image Source URL", "Owner confirmed authorization for SmartKey to reuse the source catalog images on 2026-07-21."],
  ["Verification Status", "Current records are captured from the source but are not supplier-confirmed."],
  ["Availability", "No stock claim is made. Buyer must request current availability."],
  ["Representative Disclosure", "SmartKey is an authorized sales representative and sourcing coordinator, not the manufacturer."],
  ["Source URL", "Audit trail back to the Chemportal product page."],
  ["Last Reviewed", "Date of this extraction; update after supplier or technical review."],
];
guide.getRange(`A1:B${guideRows.length}`).values = guideRows;
guide.getRange("A1:B1").format = { fill: dark, font: { bold: true, color: "#FFFFFF" } };
guide.getRange(`A2:B${guideRows.length}`).format = { wrapText: true, verticalAlignment: "top" };
guide.getRange("A:A").format.columnWidth = 26;
guide.getRange("B:B").format.columnWidth = 86;
guide.getRange("A2:A9").format = { fill: light, font: { bold: true, color: dark } };

await fs.mkdir(path.dirname(outputPath), { recursive: true });
await fs.mkdir(previewDir, { recursive: true });
for (const [sheetName, range] of [["Overview", "A1:H26"], ["Products", "A1:O12"], ["Technical Properties", "A1:I18"], ["Field Guide", "A1:B9"]]) {
  const preview = await workbook.render({ sheetName, range, scale: 1, format: "png" });
  await fs.writeFile(path.join(previewDir, `${sheetName.replaceAll(" ", "-").toLowerCase()}.png`), new Uint8Array(await preview.arrayBuffer()));
}

const inspect = await workbook.inspect({ kind: "table", range: "Overview!A1:H26", include: "values,formulas", tableMaxRows: 30, tableMaxCols: 10 });
console.log(inspect.ndjson);
const errors = await workbook.inspect({ kind: "match", searchTerm: "#REF!|#DIV/0!|#VALUE!|#NAME\\?|#N/A", options: { useRegex: true, maxResults: 300 }, summary: "final formula error scan" });
console.log(errors.ndjson);

const output = await SpreadsheetFile.exportXlsx(workbook);
await output.save(outputPath);
console.log(JSON.stringify({ products: products.length, technicalRows: technicalRows.length, outputPath, previewDir }));
