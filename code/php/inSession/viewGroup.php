<?php
	session_start();
	require_once '../../sql_connect.php';
	if(isset($_SESSION['logon'])){
		if(!$_SESSION['logon']){ 
			header("Location: ../index/home.php");
			die();
		}
	}
	else{
		header("Location: ../index/home.php");
	}

	//check if TA user is logged in
	$TA = false;
	if(isset($_SESSION['ta'])){
		$GLOBALS['TA'] = true;
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Log in</title>
		<meta charset="UTF-8" />
		<link rel="stylesheet" type="text/css" href="../../css/viewGroup.css"/>
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
		<script type="text/javascript">
			var $j = jQuery.noConflict();

			$j(document).ready(function(){
			    $j("a[href='logOut.php']").click(function(e){
			  		//call the internal disconnect function of phpfreechat
					pfc.connect_disconnect();
			    });
			});
		</script>
		<link rel="shortcut icon" href="../../../pictures/favicon.ico" type="image/x-icon">
		
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="../../js/animsition/animsition.min.css">

		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	</head>

	<body>
	<div class="animsitionMyProfile">
		<nav class="navbar navbar-inverse">
			<div class="container-fluid">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>                        
					</button>
					<a class="navbar-brand">Moodle 2.0</a>
				</div>
				<div class="collapse navbar-collapse" id="myNavbar">
					<ul class="nav navbar-nav">
						<li><a href="myProfile.php"><span class="glyphicon glyphicon-user"></span> My Profile</a></li>
						<li><a href="chat.php"><span class="glyphicon glyphicon-comment"></span> Chat</a></li>
						<li><a href="logOut.php"><span class="glyphicon glyphicon-log-out"></span> Log Out</a></li>
					</ul>
				</div>
			</div>
		</nav>
		<div id="page-content">

					
			
		<div class="row" style="text-align:center">
	</div>	<!-- COL 1 -->
			
			<div class="col-sm-6" style="text-align:center">
			<h1> Uploaded by TA </h1>
			<p> This will show the list of all uploaded documents by the TA available for the students </p>
			</div> <!-- COL 2 -->
			
			
			
			<div class="col-sm-6" style="text-align:center">
			<h1> Names and emails </h1>
			<?php
			
			if(!$TA){
			//displays all students from each group of each class
					for( $i = 1; $i <= $_SESSION['total']; $i++){		//reiterates over my classes
						//find my TA first
						$c = "class$i";
						$s = "section$i";
						$c = $_SESSION[$c];
						$s = $_SESSION[$s];
						$p = "project$i";
						$p = $_SESSION[$p];
						$queryFindTA = mysqli_query($dbc, "SELECT * FROM ClassList WHERE class = '$c' AND section = '$s'");
						$rowTA = mysqli_fetch_assoc($queryFindTA);
						$myTA = $rowTA['ta'];
						// second find my teamates
						$queryFindTeamates = mysqli_query($dbc, "SELECT * FROM Project WHERE ta = '$myTA' AND pid ='$p'");
						echo "TEAM for $c $s $p :" . "</br>";
						while( $rowTeam = mysqli_fetch_assoc($queryFindTeamates)){

							$sid = $rowTeam['sid'];
							//find that students name and email
							$queryStudentInfo = mysqli_query($dbc, "SELECT * FROM Student WHERE sid = '$sid'");
							$rowStudent = mysqli_fetch_assoc($queryStudentInfo);
							$name = $rowStudent['name'];
							$email = $rowStudent['email'];
							echo $sid . " ". $name . " " . $email . "</br>";
						}

					}
			}
			else {
				if($TA){

 					echo $_SESSION['class'];
 					echo $_SESSION['section']."</br>";
 					$ta = $_SESSION['ta'];
 					$result = mysqli_query($dbc,"SELECT * FROM Project WHERE ta='$ta'");
  
 					while( $row = mysqli_fetch_assoc($result)){
 						echo $row['sid']."</br>";
 					}
				}
			}
			
			?>
			</div> <!-- COL 3 -->
       </div>    <!-- ROW --> 
	   <div class="row" style="text-align:center">
			<div class="col-sm-12" style="text-align:center">
					<h1> Upload a file </h1>
			<?php
				//for students to upload
				if(!$TA){

					$sid = $_SESSION['sid'];
					

					if(isset($_POST['upload']) && $_FILES['file']['size'] > 0){
						$fileName = $_FILES['file']['name'];
						$tmpName  = $_FILES['file']['tmp_name'];
						$fileSize = $_FILES['file']['size'];
						$fileType = $_FILES['file']['type'];

						$pid = $_POST['pid'];
						$class = $_POST['class'];
						$section = $_POST['section'];

						$findTAQueryRes = mysqli_query($dbc, "SELECT * FROM ClassList where class = '$class' AND section = '$section'");
						$row = mysqli_fetch_assoc($findTAQueryRes);

						$fp = fopen($tmpName, 'r');
						$content = fread($fp, filesize($tmpName));
						$content = addslashes($content);
						fclose($fp);

						if(!get_magic_quotes_gpc()){
							$fileName = addslashes($fileName);
						}
						
						//fid is incremented automatically so ignore
						$fileUploadQuery = "INSERT INTO Files ( ta, fname, size, type, content, pid) 
						VALUES ('$ta', '$fileName', '$fileSize', '$fileType', '$content', '$pid')";

						if(mysqli_query($dbc, $fileUploadQuery)) {
							echo "<br>File $fileName uploaded<br>";					
						}
						else echo mysqli_error($dbc);
					} 
				}
				else{
					//TA upload
					if(isset($_POST['upload']) && $_FILES['file']['size'] > 0){
						$fileName = $_FILES['file']['name'];
						$tmpName  = $_FILES['file']['tmp_name'];
						$fileSize = $_FILES['file']['size'];
						$fileType = $_FILES['file']['type'];

						$ta = $_SESSION['ta'];

						$fp = fopen($tmpName, 'r');
						$content = fread($fp, filesize($tmpName));
						$content = addslashes($content);
						fclose($fp);

						if(!get_magic_quotes_gpc()){
							$fileName = addslashes($fileName);
						}
						//fid is incremented automatically so ignore
						//ta uploads with no pid so whole class can access documents
						$fileUploadQuery = "INSERT INTO Files ( ta, fname, size, type, content) 
						VALUES ('$ta', '$fileName', '$fileSize', '$fileType', '$content')";

						if(mysqli_query($dbc,$fileUploadQuery)) {
							echo "<br>File $fileName uploaded<br>";
							
						}
						else echo mysqli_error($dbc);
					} 
				}

			?>
			
			<div class="container-fluid">
			<form method="post" enctype="multipart/form-data" class="uploadForm">
					<table width="250px" border="0" cellpadding="1" cellspacing="1" class="uploadTable">
						<tr> 
							<td width="246px">
								<input type="hidden" name="MAX_FILE_SIZE" value="2000000">
								<input name="file" type="file" id="file" class="fileInput" required> 
								<input type='text' name='pid' placeholder='Project ID' <?php if(!TA)echo "required";?>>
								<input type='text' name='class' placeholder='Class' <?php if(!TA)echo "required";?>>
								<input type='text' name='section' placeholder='Section' <?php if(!TA)echo "required";?>>
									
								
							</td>
							<td width="80">
								<input name="upload" type="submit" class="uploadButton" id="upload" value=" Upload ">
							</td>
						</tr>
					</table>
			</form>
			</div>
		

		<br>
		<br>
			
			</div> <!-- COL 12 -->
	   </div> <!-- ROW -->
    <footer style="background-color: #222222;padding: 25px 0;color: rgba(255, 255, 255, 0.3);text-align: center;position:relative;top:-20px;">
			<div class="container">
				<p style="font-size: 12px; margin: 0;">&copy; Winter 2017 SOEN341 Project. All Rights Reserved.
				<br/>Contact Us: 1-800-123-4567
				</p>
			</div>
    </footer>
	
	<script src="../../js/jquery-1.11.2.min.js"></script>
	<script src="../../js/animsition/animsition.min.js"></script>
	<script src="../../js/sticky/jquery.sticky.js"></script>
	<script type="text/javascript" src="../../js/main.js"></script>

	</body>
</html>