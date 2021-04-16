{extends file='generic_installstep.tpl'}
{block name='installstep_title'}<span class="far fa-user"></span> Frontend admin user{/block}
{block name='installstep_description'}The administrator is a user that can do anything here{/block}
{block name='installstep_form' nocache}
{assign var="_inputid" value="adminInputEmail"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Email address' _FA_icon='fas fa-at'}
                <input type="email" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{$params[$_inputid]|escape:'htmlall'}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Should be a valid mail. It will remain private' nocache}

{assign var="_inputid" value="adminInputPassword"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Password' _FA_icon='fas fa-key'}
                <input type="password" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{$params[$_inputid]|escape:'htmlall'}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext="Your password must be $minpwd-$maxpwd characters long, can contain letters, numbers, spaces, some special characters" nocache}

{/block}
