
<!-- Block lastviewedproducts -->

<div id="mymodule_block_home" class="block">
  <h4>{l s='Welcome!' mod='lastviewedproducts'}</h4>
  <div class="block_content">
    <p>Hello </p>
  </div>
  <section class="featured-products clearfix">
    <div class="products">
        {foreach from=$products item="product"}
            {include file="catalog/_partials/miniatures/product.tpl" product=$product}
        {/foreach}
    </div>
  </section>
</div>
<script type="text/javascript">
    var lastviewedproducts_ajax_url = '{$ajax_call_url}';    
</script>

<!-- /Block lastviewedproducts -->