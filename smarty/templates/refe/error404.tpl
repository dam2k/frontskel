{extends file='error.tpl'}
{block name='title'}{if empty($title)}HTTP Error 404{/if}{/block}
{block name='error'}
                <h2>404 Not Found</h2>
                <div class="alert alert-danger" role="alert"><span class="far fa-frown-open"></span> Sorry, an error has occured, Requested page not found!</div>
{/block}
