{extends file='generic_installstep.tpl'}
{block name='installstep_title'}<span class="fas fa-edit"></span> Logger{/block}
{block name='installstep_description'}You already know the importance of logging things{/block}
{block name='installstep_smalldescription'}<p>Our logging framework is implemented with <a href="https://github.com/Seldaek/monolog">Monolog</a>.</p>{/block}
{block name='installstep_form' nocache}

{assign var="_inputid" value="adminInputLoggerName"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Channel name' _FA_icon='fas fa-file-signature'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultloggername}{/if}" placeholder="{$defaultloggername}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Logger channel name' nocache}

{assign var="_inputid" value="adminInputLoggerDateFormat"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Date format' _FA_icon='far fa-clock'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultloggerdateformat}{/if}" placeholder="{$defaultloggerdateformat}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Logger date format. For more informations, please check the <a href="https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md#customizing-the-log-format">Monolog manual</a>' nocache}

{assign var="_inputid" value="adminInputLoggerLogFormat"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Log format' _FA_icon='fas fa-align-left'}
                <input type="text" class="form-control{if $formerr[$_inputid]} is-invalid{else}{if isset($params[$_inputid])} is-valid{/if}{/if}" id="{$_inputid}" name="{$_inputid}" aria-describedby="{$_inputid}Help" value="{if isset($params[$_inputid])}{$params[$_inputid]|escape:'htmlall'}{else}{$defaultloggerlogformat}{/if}" placeholder="{$defaultloggerlogformat}" required>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='Seems cool' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Log format. For more informations, please check the <a href="https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md#customizing-the-log-format">Monolog manual</a>' nocache}

{assign var="_inputid" value="adminInputLoggerLevels"}{include 'formgroup_begin.tpl' _inputname=$_inputid _inputlabel='Verbosity' _FA_icon='fas fa-layer-group'}
{*
				&nbsp;
				<div class="form-check form-check-inline">
				  <abbr title="">Level</abbr>
				</div>
*}
				<select class="custom-select" id="adminInputLoggerLevel" name="adminInputLoggerLevel" required>
					<option value="DEBUG" {if not isset($params['adminInputLoggerLevel'])}{if $defaultloggerlevel eq "DEBUG"}selected{/if}{else}{if $params['adminInputLoggerLevel'] eq "DEBUG"}selected{/if}{/if}>DEBUG</option>
					<option value="INFO" {if not isset($params['adminInputLoggerLevel'])}{if $defaultloggerlevel eq "INFO"}selected{/if}{else}{if $params['adminInputLoggerLevel'] eq "INFO"}selected{/if}{/if}>INFO</option>
					<option value="NOTICE" {if not isset($params['adminInputLoggerLevel'])}{if $defaultloggerlevel eq "NOTICE"}selected{/if}{else}{if $params['adminInputLoggerLevel'] eq "NOTICE"}selected{/if}{/if}>NOTICE</option>
					<option value="WARNING" {if not isset($params['adminInputLoggerLevel'])}{if $defaultloggerlevel eq "WARNING"}selected{/if}{else}{if $params['adminInputLoggerLevel'] eq "WARNING"}selected{/if}{/if}>WARNING</option>
					<option value="ERROR" {if not isset($params['adminInputLoggerLevel'])}{if $defaultloggerlevel eq "ERROR"}selected{/if}{else}{if $params['adminInputLoggerLevel'] eq "ERROR"}selected{/if}{/if}>ERROR</option>
					<option value="CRITICAL" {if not isset($params['adminInputLoggerLevel'])}{if $defaultloggerlevel eq "CRITICAL"}selected{/if}{else}{if $params['adminInputLoggerLevel'] eq "CRITICAL"}selected{/if}{/if}>CRITICAL</option>
					<option value="ALERT" {if not isset($params['adminInputLoggerLevel'])}{if $defaultloggerlevel eq "ALERT"}selected{/if}{else}{if $params['adminInputLoggerLevel'] eq "ALERT"}selected{/if}{/if}>ALERT</option>
					<option value="EMERGENCY" {if not isset($params['adminInputLoggerLevel'])}{if $defaultloggerlevel eq "EMERGENCY"}selected{/if}{else}{if $params['adminInputLoggerLevel'] eq "EMERGENCY"}selected{/if}{/if}>EMERGENCY</option>
				</select>
{include 'formgroup_end.tpl' _inputname=$_inputid _valid_feedback_arr='' _invalid_feedback_arr=$formerr[$_inputid] _helptext='Set desired logging level.' nocache}

{/block}
