{extends file='genericpage.tpl'}
{block name='head'}{/block}
{block name="title"}My great site - Dashboard{/block}
{block name='body'}
  <div class="container-fluid">
    <p>You are logged in safely and statelessly (<strong>without any php session</strong>) into a protected area (uid: <strong>{$uid}</strong>)</p>
  </div>
{/block}
