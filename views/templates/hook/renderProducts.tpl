
<section>
  <h2>
    {if $products|@count == 1}
      <span>{l s='Welcome!' mod='lastviewedproducts'}</span>
    {else}
     <span>{l s='Welcome!' mod='lastviewedproducts'}</span>
    {/if}
  </h2>
  <div>

      {foreach from=$products item="product"}
          {include file="catalog/_partials/miniatures/product.tpl" product=$product}
      {/foreach}
  </div>
</section>