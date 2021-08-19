{extends file='genericpage.tpl'} 
{* Login page from https://startbootstrap.com/snippets/login/ *}
{block name='head'}
    <style>
        body {
          background: #007bff;
          background: linear-gradient(to right, #0062E6, #33AEFF);
        }
    </style>
{/block}
{block name="title"}Bye!{/block}
{block name='body' nocache}
  <div class="container">
    <div class="row">
      <div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
        <div class="card card-signin my-4">
          <div class="card-body">
            <h5 class="card-title text-center">Logged out</h5>
            <form class="p-4 p-md-4 border rounded-3 bg-light mb-3">
              <div class="form-floating mb-3">
                <a href="{$basePath|default:'/'}" class="w-100 btn btn-lg btn-primary"><span class="glyphicon glyphicon-home"></span>Take Me Home</a>
              </div>
              <hr class="my-4">
              <small class="text-muted">Good bye my friend. Come back soon.</small>
            </form>
          </div>
        </div>

{*
        <div class="error-actions">
        </div>
*}
    </div>
  </div>
{/block}
