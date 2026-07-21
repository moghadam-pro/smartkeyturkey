<?php

namespace SmartKeyTurkey\Core;

defined( 'ABSPATH' ) || exit;

final class Analytics {
	public static function init(): void {
		add_action( 'wp_enqueue_scripts', array( self::class, 'enqueue' ), 30 );
	}

	public static function enqueue(): void {
		if ( ! is_singular( array( 'skt_product', 'skt_property' ) ) ) { return; }
		wp_register_script( 'smartkey-conversions', '', array(), SKT_CORE_VERSION, true );
		wp_enqueue_script( 'smartkey-conversions' );
		$script = <<<'JS'
(function(){
  window.dataLayer=window.dataLayer||[];
  function record(eventName,detail){
    var key=eventName+'-'+(detail.item_id||detail.item_name||'general');
    window.sktRecordedConversions=window.sktRecordedConversions||{};
    if(window.sktRecordedConversions[key]){return;}
	window.dataLayer.push(Object.assign({event:eventName,lead_source:'website'},detail));
    window.sktRecordedConversions[key]=true;
  }
  document.addEventListener('wpcf7mailsent',function(event){
    var form=event.target;
    if(!form||!form.closest('.skt-rfq-section')){return;}
    var product=form.querySelector('[name="product-grade"]');
    record('skt_petrochemical_rfq',{lead_type:'petrochemical',item_name:product?product.value:''});
  });
  if(document.body.classList.contains('single-skt_property')&&new URLSearchParams(location.search).get('inquiry')==='sent'){
    record('skt_property_inquiry',{lead_type:'property',item_id:document.body.className.match(/postid-(\d+)/)?.[1]||''});
  }
})();
JS;
		wp_add_inline_script( 'smartkey-conversions', $script );
	}
}
