{extends file='generic_installstep.tpl'}
{block name='installstep_title'}<span class="fas fa-cookie"></span> Login cookie{/block}
{block name='installstep_description'}JWT tokens are stored into an encrypted login cookie{/block}
{block name='installstep_smalldescription'}<p>After a client's valid login we generate a cookie and we save its login tokens into it in a very secure way using software encryption. When the client requests another page in our site, it can send this cookie to us so that we can read it and understand who it is and maintain a valid stateless session. This is what login cookie is needed for.</p>{/block}
{block name='installstep_form' nocache}

{assign var="_inputid" value="adminInputLCName"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Cookie name' _FA_icon='far fa-address-card'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultlcname}{/if}" placeholder="{$defaultlcname}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Login cookie name' nocache}

{assign var="_inputid" value="adminInputLCKey"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Key' _FA_icon='fas fa-key'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultlckey}{/if}" placeholder="{$defaultlckey}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Key used to handle the login cookie. Use 64 mixed random chars from 0 to 9 or from a to f' nocache}

{assign var="_inputid" value="adminInputLCSalt"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Salt' _FA_icon='fas fa-random'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultlcsalt}{/if}" placeholder="{$defaultlcsalt}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Salt used to scramble the login cookie. Use 24 mixed random chars from 0 to 9 or from a to f' nocache}

{assign var="_inputid" value="adminInputLCPath"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Path' _FA_icon='fas fa-terminal'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultlcpath}{/if}" placeholder="{$defaultlcpath}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Set a valid login cookie path.' nocache}

{*
TODO: add login cookie attributes (HostOnly, Secure, HTTPOnly, SameSite)
*}
{/block}
