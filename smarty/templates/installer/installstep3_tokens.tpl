{extends file='generic_installstep.tpl'}
{block name='installstep_title'}<span class="fas fa-signature"></span> Access and Refresh Tokens{/block}
{block name='installstep_description'}JWT tokens are stateful security mechanisms used to grant access to web resources via cryptography{/block}
{block name='installstep_smalldescription'}<p>A refresh token is granted after a successful authentication, it is used to release an access token. Both tokens has expiration time that you can set here. When an access token is expired, a new one is released automatically from a valid refresh token. When the refresh token expires, you can set if it can be automatically renewed or not. Refresh token can also be revoked, so that at access token expiration the user will be logged out.</p>{/block}
{block name='installstep_form' nocache}
<p><strong>Refresh token</strong></p>
{assign var="_inputid" value="adminInputRTKey"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Key' _FA_icon='fas fa-sync-alt'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultrtkey}{/if}" placeholder="{$defaultrtkey}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Key used to handle the JWT token. Use 64 mixed random chars from 0 to 9 or from a to f' nocache}

{assign var="_inputid" value="adminInputRTTimeSkew"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Time skew' _FA_icon='fas fa-stopwatch'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultrtts}{/if}" placeholder="{$defaultrtts}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Time skew tolerance in seconds before new issue and after expiration for the refresh token. Don\'t keep to high (0 to 30 should be enough!)' nocache}

{*
TODO: write this:
            'expire' => 10512000, // token expiration time in seconds (it should be set very high: weeks, months, even years... I like 10512000 s: 4 months)
            'auto_refresh' => 7200, // token is auto refreshed if there is activity auto_refresh seconds before expiration; 0: token expires naturally

<p><strong>Access token</strong></p>
{assign var="_inputid" value="adminInputDBDriver"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='DB Driver' _FA_icon='fas fa-screwdriver'}
                <select class="custom-select form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" required>
                  <option value="" >Choose driver</option>
{foreach $dbdrivers as $dbdriver}
                  <option value="{$dbdriver}" {if (not $loaded_dbdrivers[$dbdriver])}disabled {else}{if ($params[$_inputid] eq $dbdriver)}selected {/if}{/if}>{$dbdriver}</option>
{/foreach}
                </select>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Driver used to talk to the database. If your driver is supported but missing try to install the proper PHP extension, then refresh this page' nocache}
*}
{/block}
