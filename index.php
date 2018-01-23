<html lang="en-US">
<head>
<title>LSC index</title>
</head>
<body>
<?php

require __DIR__ . '/lsc_db.php';
LSC_DB::create_db();
LSC_DB::create_members_table();

?>
<h1>LSC Home</h1>
<a href="members.php?action=create">create member</a>
<a href="members.php?action=list">list members</a>
</body>
</html>
