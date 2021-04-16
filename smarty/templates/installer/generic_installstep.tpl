{extends file='genericpage.tpl'}
{block name='head'}{/block}
{block name="title"}FrontSkel - INSTALLER - STEP {$step}{/block}
{block name='body'}
  <div class="container-fluid">
    <div class="text-center">
      <h1 class="display-4">{block name='installstep_title'}{/block}</h1>
      <h5>{block name='installstep_description'}{/block}</h5>
      <form method="post" action="{$basePath}/install/{$step}">
        <div class="row">
          <div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
{block name='installstep_form'}{/block}
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-lg" name="{$formstep}" value="{$formforward}">Next <span class="fas fa-step-forward"></span></button>
        <button type="submit" class="btn btn-secondary btn-lg" name="{$formstep}" value="{$formbackward}"><span class="fas fa-step-backward"></span> Previous</button>
      </form>
    </div>
  </div>
{/block}
