<div style="padding:20px;">
{if $__wcf->session->getPermission('mod.iplog.canSeeIPHistory')}
	{if !$ipAddressEntries}
	<p class="info">{lang}wcf.iplog.messages.userNoIPs{/lang}</p>
	{else}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.user.iplog.profile.lastips{/lang}</h2>
		</header>
			
		<table class="table">
			<thead>
				<tr>
					<th class="columnIpAddress">{lang}wcf.user.iplog.general.ipaddress{/lang}</th>
					<th class="columnDate">{lang}wcf.user.iplog.general.date{/lang}</th>
		        </tr>
			</thead>
			<tbody>
	            {foreach from=$ipAddressList item='ip'}
				<tr>
					<td class="columnIpAddress">{$ip['ipAddress']}</td>
	                <td class="columnDate">{$ip['timestamp']|date}</td>
				</tr>
	            {/foreach}
	        </tbody>
	    </table>
	</div>
	{/if}
{/if}

{if $__wcf->session->getPermission('mod.iplog.canSeeMultiAccounts')}
	{if !$multiaccountEntries}
	<p class="info">{lang}wcf.iplog.messages.userNoMultiAccounts{/lang}</p>
	{else}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.user.iplog.profile.multiaccounts{/lang}</h2>
		</header>
			
		<table class="table">
			<thead>
				<tr>
					<th class="columnAvatar" style="width:32px;"></th>
					<th class="columnMultiaccount">{lang}wcf.user.iplog.general.multiaccount{/lang}</th>
					<th class="columnIpAddress">{lang}wcf.user.iplog.general.ipaddress{/lang}</th>
					<th class="columnDate">{lang}wcf.user.iplog.general.date{/lang}</th>
		        </tr>
			</thead>
			<tbody>
	            {foreach from=$multiaccounts item='ma'}
				<tr>
					<td class="columnAvatar"><span class="framed">{@$ma['user']->getAvatar()->getImageTag(32)}</span></td>
					<td class="columnMultiaccount">
						<a href="{link controller='User' id=$ma['user']->userID title=$ma['user']->username}{/link}" class="userLink" data-user-id="{$ma['user']->userID}">
							{$ma['user']->username}
						</a>
					</td>
					<td class="columnIpAddress">{$ma['ipAddress']}</td>
	                <td class="columnDate">{$ma['timestamp']|date}</td>
				</tr>
	            {/foreach}
	        </tbody>
	    </table>
	</div>
	{/if}
{/if}
</div>
