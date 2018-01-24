<?php

require __DIR__ . '/lsc_db.php';
LSC_DB::create_db();
LSC_DB::create_members_table();
LSC_DB::create_sections_table();

$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
$id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : '';
$create = isset( $_REQUEST['create'] ) ? $_REQUEST['create'] : '';
$cancel = isset( $_REQUEST['cancel'] ) ? $_REQUEST['cancel'] : '';
$update = isset( $_REQUEST['update'] ) ? $_REQUEST['update'] : '';
$edit = isset( $_REQUEST['edit'] ) ? $_REQUEST['edit'] : '';
$delete = isset( $_REQUEST['delete'] ) ? $_REQUEST['delete'] : '';

if ( $create || $update ) {

	$name = isset( $_REQUEST['name'] ) ? $_REQUEST['name'] : '';
	$admin_member_id = isset( $_REQUEST['admin_member_id'] ) ? $_REQUEST['admin_member_id'] : '';

	if ( $create ) {
		LSC_DB::create_section( $name, $admin_member_id );
	} else {
		LSC_DB::update_section( $id, $name, $admin_member_id );
	}

	$location = 'index.php';
	$status = 302;
	header( "Location: $location", true, $status );
	exit;
} elseif ( $delete ) {
	$id = (int) substr( $delete, strlen( 'Delete ' ) );
	
	LSC_DB::delete_section( $id );

	$location = 'index.php';
	$status = 302;
	header( "Location: $location", true, $status );
	exit;
} elseif ( $cancel ) {
	$location = 'index.php';
	$status = 302;
	header( "Location: $location", true, $status );
	exit;
}

?>
<html lang="en-US">
<head>
<title>LSC Sections</title>
</head>
<body>
<?php

if ( $edit || $action === 'create' ) {
	if ( $edit ) {
		$id = (int) substr( $edit, strlen( 'Edit ' ) );
		list( $name, $admin_member_id ) = LSC_DB::get_section( $id );
	} else {
		$id = $name = $admin_member_id = '';
	}
	$members = LSC_DB::list_members();
?>
<h1><?php echo $edit ? 'Update' : 'Create'; ?> Section</h1>
<a href="index.php">home</a>
<a href="sections.php?action=list">list sections</a>
<form>
<input type="hidden" name="action" value="<?php echo $action; ?>">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<table>
<tr>
<td>Name:</td><td><input type="text" name="name" value="<?php echo htmlspecialchars( $name, ENT_COMPAT); ?>" size="100"></td>
</tr>
<tr>
<td>Admin Member ID:</td>
<td>
<select name="admin_member_id">
<option value="0"<?php if ( $admin_member_id === 0 ) echo ' selected'; ?>></option>
<?php
foreach ( $members as $member ) {
?>
<option value="<?php echo $member['id']; ?>"<?php if ( $admin_member_id === $member['id'] ) echo ' selected'; ?>><?php echo htmlspecialchars( $member['name'], ENT_COMPAT ); ?></option>
<?php
}
?>
</select>
</td>
</tr>
<tr>
<td colspan="2">
<input type="submit" name="<?php echo $edit ? 'update' : 'create'; ?>" value="<?php echo $edit ? 'Update' : 'Create'; ?>">
<input type="submit" name="cancel" value="Cancel">
</td>
</form>
<?php
} elseif ( $action === 'list' ) {
	$rows = LSC_DB::list_sections();
?>
<h1>Lists Sections</h1>
<a href="index.php">home</a>
<a href="sections.php?action=create">create sections</a>
<form>
<table>
<tr><td>ID</td><td>Name</td><td>Admin Member ID</td><td>Admin Member Name</td></tr>
<?php
	foreach ( $rows as $row ) {
?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['admin_member_id']; ?></td>
<td><?php echo $row['admin_member_name']; ?></td>
<td><input type="submit" name="edit" value="Edit <?php echo $row['id']; ?>"></td>
<td><input type="submit" name="delete" value="Delete <?php echo $row['id']; ?>"></td>
</tr>
<?php
	}
?>
</table>
</form>
<?php
}
?>
</body>
</html>

