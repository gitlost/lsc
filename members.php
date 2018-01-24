<?php

require __DIR__ . '/lsc_db.php';
LSC_DB::create_db();
LSC_DB::create_members_table();

$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '';
$id = isset( $_REQUEST['id'] ) ? $_REQUEST['id'] : '';
$create = isset( $_REQUEST['create'] ) ? $_REQUEST['create'] : '';
$cancel = isset( $_REQUEST['cancel'] ) ? $_REQUEST['cancel'] : '';
$update = isset( $_REQUEST['update'] ) ? $_REQUEST['update'] : '';
$edit = isset( $_REQUEST['edit'] ) ? $_REQUEST['edit'] : '';
$delete = isset( $_REQUEST['delete'] ) ? $_REQUEST['delete'] : '';

if ( $create || $update ) {

	$name = isset( $_REQUEST['name'] ) ? $_REQUEST['name'] : '';
	$address = isset( $_REQUEST['address'] ) ? $_REQUEST['address'] : '';
	$address2 = isset( $_REQUEST['address2'] ) ? $_REQUEST['address2'] : '';
	$phone = isset( $_REQUEST['phone'] ) ? $_REQUEST['phone'] : '';
	$email = isset( $_REQUEST['email'] ) ? $_REQUEST['email'] : '';
	$gender = isset( $_REQUEST['gender'] ) ? $_REQUEST['gender'] : '';
	$is_exec = isset( $_REQUEST['is_exec'] ) ? $_REQUEST['is_exec'] : '';

	if ( $create ) {
		LSC_DB::create_member( $name, $address, $address2, $phone, $email, $gender, $is_exec );
	} else {
		LSC_DB::update_member( $id, $name, $address, $address2, $phone, $email, $gender, $is_exec );
	}

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

?>
<html lang="en-US">
<head>
<title>LSC Members</title>
</head>
<body>
<?php

if ( $edit || $action === 'create' ) {
	if ( $edit ) {
		$id = (int) substr( $edit, strlen( 'Edit ' ) );
		list( $name, $address, $address2, $phone, $email, $gender, $is_exec ) = LSC_DB::get_member( $id );
	} else {
		$id = $name = $address = $address2 = $phone = $email = $gender = $is_exec = '';
	}
?>
<h1><?php echo $edit ? 'Update' : 'Create'; ?> Member</h1>
<a href="index.php">home</a>
<a href="members.php?action=list">list members</a>
<form>
<input type="hidden" name="action" value="<?php echo $action; ?>">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<table>
<tr>
<td>Name:</td><td><input type="text" name="name" value="<?php echo htmlspecialchars( $name, ENT_COMPAT); ?>" size="100"></td>
</tr>
<tr>
<td>Address:</td><td><input type="text" name="address" value="<?php echo htmlspecialchars( $address, ENT_COMPAT); ?>" size="100"></td>
</tr>
<tr>
<td>Address2:</td><td><input type="text" name="address2" value="<?php echo htmlspecialchars( $address2, ENT_COMPAT); ?>" size="100"></td>
</tr>
<tr>
<td>Phone:</td><td><input type="text" name="phone" value="<?php echo htmlspecialchars( $phone, ENT_COMPAT); ?>" size="100"></td>
</tr>
<tr>
<td>Email:</td><td><input type="text" name="email" value="<?php echo htmlspecialchars( $email, ENT_COMPAT); ?>" size="100"></td>
</tr>
<tr>
<td>Gender:</td>
<td>
<label><input type='radio' name='gender' value='M'<?php if ( $gender !== 'F' ) echo ' checked'; ?>>Male</label>
<label><input type='radio' name='gender' value='F'<?php if ( $gender === 'F' ) echo ' checked'; ?>>Female</label>
</td>
</tr>
<tr>
<td>Exec?</td>
<td>
<label><input type='radio' name='is_exec' value='1'<?php if ( $is_exec ) echo ' checked'; ?>>Yes</label>
<label><input type='radio' name='is_exec' value='0'<?php if ( ! $is_exec ) echo ' checked'; ?>>No</label>
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
	$rows = LSC_DB::list_members();
?>
<h1>Lists Members</h1>
<a href="index.php">home</a>
<a href="members.php?action=create">create members</a>
<form>
<table>
<tr><td>ID</td><td>Name</td><td>Address</td><td>Address2</td><td>Phone</td><td>Email</td><td>Gender</td><td>Exec?</td><td></td><td></td></tr>
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

