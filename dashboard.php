<?php
/**
 * Include configuration
 */
include_once('configs/config.inc.php');

$users = getUsers();

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Dashboard</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="<?php echo $GLOBALS['config']['cms']['design_path'].'js/register.js';?>"></script>
</head>
<body>

<div class="container">
    <a href="logout.php"><button style="float: right; margin: 40px" class="btn btn-danger"> Logout</button></a>
  <h2>User List</h2>
  <table class="table">
    <thead>
      <tr>
        <th>Id</th>
        <th>Email</th>
        <th>User Name</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php
        foreach ($users as $user) { ?>
            <tr>
                <td><?php echo $user['id'];?></td>
                <td><?php echo $user['email'];?></td>
                <td><?php echo $user['user_name'];?></td>
                <td><label style="color: red" class="delete-item" data-id="<?php echo $user['id']; ?>">delete</label></td>
          </tr>
      <?php  } ?>
    </tbody>
  </table>
</div>

</body>
</html>
