<html lang="en-US">
<head>
<title>LSC Members</title>
</head>
<body>
<?php

require __DIR__ . '/lsc_db.php';
LSC_DB::create_db();
LSC_DB::create_members_table();

$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
$id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : '';
$create = isset( $_REQUEST['create'] ) ? $_REQUEST['create'] : '';
$cancel = isset( $_REQUEST['cancel'] ) ? $_REQUEST['cancel'] : '';
$delete = isset( $_REQUEST['delete'] ) ? $_REQUEST['delete'] : '';

if ( $create ) {

	$name = isset( $_REQUEST['name'] ) ? $_REQUEST['name'] : '';
	$address = isset( $_REQUEST['address'] ) ? $_REQUEST['address'] : '';
	$address2 = isset( $_REQUEST['address2'] ) ? $_REQUEST['address2'] : '';
	$phone = isset( $_REQUEST['phone'] ) ? $_REQUEST['phone'] : '';
	$email = isset( $_REQUEST['email'] ) ? $_REQUEST['email'] : '';
	$gender = isset( $_REQUEST['gender'] ) ? $_REQUEST['gender'] : '';
	$is_exec = isset( $_REQUEST['is_exec'] ) ? $_REQUEST['is_exec'] : '';

	LSC_DB::create_member( $name, $address, $address2, $phone, $email, $gender, $is_exec );

	$location = 'index.php';
	$status = 302;
	header( "Location: $location", true, $status );
	exit;
} elseif ( $delete ) {
	$id = (int) substr( $delete, strlen( 'Delete ' ) );
	
	LSC_DB::delete_member( $id );

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

if ( $action === 'create' ) {
?>
<h1>Create Member</h1>
<a href="index.php">home</a>
<a href="members.php?action=list">list members</a>
<form>
<input type="hidden" name="action" value="<?php echo $action; ?>">
<table>
<tr>
<td>Name:</td><td><input type="text" name="name" size="100"></td>
</tr>
<tr>
<td>Address:</td><td><input type="text" name="address" size="100"></td>
</tr>
<tr>
<td>Address2:</td><td><input type="text" name="address2" size="100"></td>
</tr>
<tr>
<td>Phone:</td><td><input type="text" name="phone" size="100"></td>
</tr>
<tr>
<td>Email:</td><td><input type="text" name="email" size="100"></td>
</tr>
<tr>
<td>Gender:</td>
<td>
<label><input type='radio' name='gender' value='M'>Male</label>
<label><input type='radio' name='gender' value='F'>Female</label>
</td>
</tr>
<tr>
<td>Exec?</td>
<td>
<label><input type='radio' name='is_exec' value='1'>Yes</label>
<label><input type='radio' name='is_exec' value='0'>No</label>
</td>
</tr>
<tr>
<td colspan="2">
<input type="submit" name="create" value="Create">
<input type="submit" name="cancel" value="Cancel">
</td>
</form>
<?php
} elseif ( $action === 'list' ) {
	$rows = LSC_DB::list_members();
?>
<h1>Lists Members</h1>
<a href="index.php">home</a>
<a href="members.php?action=create">create members</a>
<form>
<table>
<tr><td>ID</td><td>Name</td><td>Address</td><td>Address2</td><td>Phone</td><td>Email</td><td>Gender</td><td>Exec?</td><td></td></tr>
<?php
	foreach ( $rows as $row ) {
?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['address']; ?></td>
<td><?php echo $row['address2']; ?></td>
<td><?php echo $row['phone']; ?></td>
<td><?php echo $row['email']; ?></td>
<td><?php echo $row['gender']; ?></td>
<td><?php echo $row['is_exec']; ?></td>
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

