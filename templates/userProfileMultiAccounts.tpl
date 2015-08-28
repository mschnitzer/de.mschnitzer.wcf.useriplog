{if $__wcf->session->getPermission('mod.iplog.canSeeIPHistory')}
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
                <td class="columnIpAddress">{$ip['timestamp']|date}</td>
			</tr>
            {/foreach}
        </tbody>
    </table>
</div>
{/if}