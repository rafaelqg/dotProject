<?php
if (!defined('DP_BASE_DIR')) {
	die('You should not access this file directly.');
}
$AppUI->savePlace();
require_once DP_BASE_DIR ."/modules/ldap_extended/ldap_extended.class.php";
$ldapExt= new CLDAPExtended();
?>
<br />
<h1 align="center">
LDAP Extended Admin console
</h1>
<br /><br />
<table class="tbl" align="center" style="width:80%;text-align:left" cellpadding="5">
<tr>
	<th>
	LDAP HOST
	</th>
	<td>
	<?php echo $ldapExt->ldap_host ?>
	</td>
</tr>

<tr>
	<th>
	LDAP PORT
	</th>
	
	<td>
	<?php echo $ldapExt->ldap_port ?>
	</td>
</tr>

<tr>
	<th>
	LDAP VERSION
	</th>
	
	<td>
	<?php echo $ldapExt->ldap_version ?>
	</td>
</tr>

<tr>
	<th>
	LDAP CONNECTION USER
	</th>
	
	<td>
	<?php echo $ldapExt->ldap_search_user ?>
	</td>
</tr>

<tr>
	<th>
	LDAP CONNECTION PASSWORD
	</th>
	
	<td>
	<?php echo $ldapExt->ldap_password ?>
	</td>
</tr>

<tr>
	<th>
	LDAP DN
	</th>
	
	<td>
	<?php echo $ldapExt->ldap_dn ?>
	</td>
</tr>

</table>


<br /><br /><br />

<table class="tbl" align="center" style="width:80%;text-align:left" cellpadding="5">
	<tr>
			<th colspan="2" align="center">
			Process LDAP Synchronization
			</th>
	</tr>
	<tr>
		<th>
			Group membership based:
		</th>
		<th>
			Memberof attribute based:
		</th>
	</tr>
	<tr>
		<td align="center">
			<form action="?m=ldap_extended" method="post">
				 <input type="hidden" name="dosql" value="do_ldap_group_membership_based" action="?m=ldap_extended" method="post" />
				<input type="submit" value="Synchronize" class="button" />
			</form>
		</td>
		<td align="center">
		<form action="?m=ldap_extended" method="post">
			<input type="hidden" name="dosql" value="do_ldap_memberof_based"  />
			<input type="submit" value="Synchronize" class="button" />
		</form>
		</td>
	</tr>
</table>