{extends file='generic_installstep.tpl'}
{block name='installstep_title'}<span class="fas fa-edit"></span> Double check{/block}
{block name='installstep_description'}OK, now check your things well{/block}
{block name='installstep_smalldescription'}<p>You can double-check if you set everything correctly. On doubt check our site at <a href="https://www.frontskel.org/">Frontskel</a>.</p>{/block}
{block name='installstep_form' nocache}
{$all_params|print_r}

{/block}
