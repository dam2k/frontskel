{extends file='generic_installstep.tpl'}
{block name='installstep_title'}<span class="fas fa-database"></span> Frontend DB details{/block}
{block name='installstep_description'}We use the database to store users, roles, expired access tokens and other useful stuff{/block}
{block name='installstep_smalldescription'}<p>Just create your db, user and permissions.</p><small class="text-muted">Eg for MySQL on localhost: <pre><code>CREATE USER 'frontskel'@'localhost' IDENTIFIED BY 'yourseuredbuserpwd';<br>CREATE DATABASE frontskel;<br>GRANT ALL PRIVILEGES ON frontskel.* to 'frontskel'@'localhost';<br>FLUSH PRIVILEGES;</code></pre></small>{/block}
{block name='installstep_form' nocache}
{assign var="_inputid" value="adminInputDBUser"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='DB User' _FA_icon='fas fa-user'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultdbuser}{/if}" placeholder="{$defaultdbuser}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Username used to connect to the DB' nocache}

{assign var="_inputid" value="adminInputDBPwd"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='DB Password' _FA_icon='fas fa-key'}
                <input type="password" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{$params[$_inputid]|escape:'htmlall'}">
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Password used to connect to the DB' nocache}

{assign var="_inputid" value="adminInputDBHost"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='DB Host' _FA_icon='fas fa-server'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{$params[$_inputid]|escape:'htmlall'}">
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='DB Hostname or IP address' nocache}

{assign var="_inputid" value="adminInputDBPort"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='DB Port' _FA_icon='fas fa-passport'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{$params[$_inputid]|escape:'htmlall'}">
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='TCP Port number used to connect to the DB' nocache}

{assign var="_inputid" value="adminInputDBName"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='DB Name' _FA_icon='fas fa-database'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultdbuser}{/if}" placeholder="{$defaultdbuser}" placeholder="{$defaultdbname}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Database name' nocache}

{assign var="_inputid" value="adminInputDBSocket"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='DB Socket' _FA_icon='fab fa-get-pocket'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{$params[$_inputid]|escape:'htmlall'}">
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='DB Unix domain socket (in case is used instead of host/port)' nocache}

{assign var="_inputid" value="adminInputDBCharset"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='DB Charset' _FA_icon='fas fa-font'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultdbcharset}{/if}" placeholder="{$defaultdbcharset}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Default charset to be used with the DB' nocache}

{assign var="_inputid" value="adminInputDBDriver"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='DB Driver' _FA_icon='fas fa-screwdriver'}
                <select class="custom-select form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" required>
                  <option value="" >Choose driver</option>
{foreach $dbdrivers as $dbdriver}
                  <option value="{$dbdriver}" {if (not $loaded_dbdrivers[$dbdriver])}disabled {else}{if ($params[$_inputid] eq $dbdriver)}selected {/if}{/if}>{$dbdriver}</option>
{/foreach}
                </select>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Driver used to talk to the database. If your driver is supported but missing try to install the proper PHP extension, then refresh this page' nocache}

{/block}
