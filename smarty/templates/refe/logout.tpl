{extends file='genericpage.tpl'} 
{* Login page from https://startbootstrap.com/snippets/login/ *}
{block name='head'}
{/block}
{block name="title"}Bye!{/block}
{block name='body' nocache}
  <div class="container">
    <div class="row">
      <div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
        <div class="card card-signin my-5">
          <div class="card-body">
            <h5 class="card-title text-center">Logged out</h5>
		Good bye my fiend. Come back soon.
          </div>
        </div>
        <div class="error-actions">
          <a href="{$basePath|default:'/'}" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-home"></span>
            Take Me Home </a>
        </div>
      </div>
    </div>
  </div>
{/block}
