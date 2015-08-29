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
</div>
