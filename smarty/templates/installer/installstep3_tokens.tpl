{extends file='generic_installstep.tpl'}
{block name='installstep_title'}<span class="fas fa-signature"></span> Access and Refresh Tokens{/block}
{block name='installstep_description'}JWT tokens are stateful security mechanisms used to grant access to web resources via cryptography{/block}
{block name='installstep_smalldescription'}<p>A refresh token is granted after a successful authentication, it is used to release an access token. Both tokens has expiration time that you can set here. When an access token is expired, a new one is released automatically from a valid refresh token. When the refresh token expires, you can set if it can be automatically renewed or not. Refresh token can also be revoked, so that at access token expiration the user will be logged out.</p>{/block}
{block name='installstep_form' nocache}
<p><strong><span class="fas fa-sync"></span> Refresh token</strong></p>
{assign var="_inputid" value="adminInputRTKey"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Key' _FA_icon='fas fa-key'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultrtkey}{/if}" placeholder="{$defaultrtkey}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Key used to handle the JWT token. Use 64 mixed random chars from 0 to 9 or from a to f' nocache}

{assign var="_inputid" value="adminInputRTTimeSkew"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Time skew' _FA_icon='fas fa-stopwatch'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultrtts}{/if}" placeholder="{$defaultrtts}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Time skew tolerance in seconds before new issue and after expiration for the refresh token. Don\'t keep to high (0 to 30 should be enough!)' nocache}

{assign var="_inputid" value="adminInputRTExpiration"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Expiration' _FA_icon='far fa-calendar-times'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultrtexp}{/if}" placeholder="{$defaultrtexp}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Refresh token expiration time in seconds (it can/should be set high: weeks, months, even years... I like 10512000 s: 4 months)' nocache}

{assign var="_inputid" value="adminInputRTAutoRefresh"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Autorefresh' _FA_icon='fas fa-sync-alt'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultrtar}{/if}" placeholder="{$defaultrtar}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Refresh token is auto refreshed if there is activity auto_refresh seconds before expiration; 0: token expires naturally' nocache}


<p><strong><span class="far fa-thumbs-up"></span> Access token</strong></p>
{assign var="_inputid" value="adminInputATKey"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Key' _FA_icon='fas fa-key'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultatkey}{/if}" placeholder="{$defaultatkey}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Key used to handle the JWT token. Use 64 mixed random chars from 0 to 9 or from a to f' nocache}

{assign var="_inputid" value="adminInputATTimeSkew"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Time skew' _FA_icon='fas fa-stopwatch'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultatts}{/if}" placeholder="{$defaultatts}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Time skew tolerance in seconds before new issue and after expiration for the refresh token. Don\'t keep to high (0 to 5 should be enough!)' nocache}

{assign var="_inputid" value="adminInputATExpiration"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Expiration' _FA_icon='far fa-calendar-times'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultatexp}{/if}" placeholder="{$defaultatexp}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Access token expiration time in seconds (it can/should be set low: 10 seconds to 5 minutes... I like 60 seconds)' nocache}

{/block}
