{extends file='genericpage.tpl'}
{block name='head'}{/block}
{block name="title"}FrontSkel - INSTALLER{/block}
{block name='body'}
  <div class="jumbotron jumbotron-fluid">
    <div class="container">
      <h1 class="display-4"><span class="far fa-hdd"></span> Welcome to the FrontSkel installer!!</h1>
      <p class="lead">FrontSkel is a simple microframework written in PHP based on bootstrap, fontawesome, slim, PHP-DI, smarty, monolog, composer
        and other cool stuff that will make your frontend web development startup easier.</p>
      <hr class="my-4">
      <p>Now we will ask you some informations to get the software ready to run. You can go backwards and forwards as you like.</p>
      <a class="btn btn-secondary btn-lg disabled" href="#" role="button">Learn more <span class="fas fa-book"></span></a>
      <a class="btn btn-primary btn-lg" href="{$basePath}/install/1" role="button">Go ahead <span class="fas fa-step-forward"></span></a>
    </div>
  </div>
{/block}
