<?php
	// If form submitted, validate input
	if (isset($_POST['login_delegate'])) {
	  $ballot_access_code = $_GET['bac'];

	  $delegate_code = $_POST["delegate_code"];

	  // Make sure code is a 10-digit number
	  if ((strlen($delegate_code) != 10) || !is_numeric($delegate_code)) {$error_code .= "You must provide a 10-digit, numeric code.<br />";}

	  if (!$ballot_access_code) {$error_code .= "You must provide a ballot code.<br />";}
	}

	// If code passes validation, search for code in spreadsheet
	if (isset($_POST['login_delegate']) && $error_code == '') {

	  // Set master code from inputs
	  $found = 0;

	  // Open spreadsheet
	  if (($handle = fopen("IndividualDelegates.csv", "r")) !== FALSE) {
	    // Interate over rows
	    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	      // If code found, break
	      if ($data[0] == $delegate_code) {
	        $found = 1;
	        $delegation_id = $data[1];
	        break;
	      }
	    }
	    fclose($handle);
	  }

	} // End search for code

	// If code passes validation, get the voting amount for the individual delegate based on the delegation

	if(isset($_POST['login_delegate']) && $found == 1)
	{
		// Ballot logic
		switch($_GET['bac'])
		{
			case 'c1':
				$ballot_id = 1;
				$form_type = "absolutes";
				$ballot_name = "Adoption of the Constitutional Amendments";
			break;
			case 'c2':
				$ballot_id = 2;
				$form_type = "absolutes";
				$ballot_name = "Adoption of the Resolutions";
			break;
			case 'e3':
				$ballot_id = 3;
				$form_type = "absolutes";
				$ballot_name = "Uncontested candidates";
			break;
			case 'x4':
				$ballot_id = 4;
				$ballot_name = "Campaign Director";
				$candidate_01 = "Candidate Name #1";
				$candidate_02 = "Candidate Name #2";
			break;
			default:
			break;
		}

		// Set default voting amount to 0
		$delegate_max_vote = 0;
		
		// Open spreadsheet
	  	if (($handle = fopen("DelegationVotes.csv", "r")) !== FALSE) {
		    // Interate over rows to look for delegation identifier
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		      // If code found, break
		      if ($data[0] == $delegation_id) {
		        // Set the delegation name
		        $delegation_name = $data[1];
		        // Set the maximum voting amount for the delegate
		        $delegate_max_vote = $data[2];
		        // Get the scriptURL for the specific chapter
		        $delegation_scriptURL = $data[7];
		        break;
		      }
		    }
		    fclose($handle);
		}
	} // End get the voting amount

	// If code not found in spreadsheet, set error code
	if (isset($_POST['login_delegate']) && !$found) {
	  $error_code .= " The code you entered is invalid. Please double-check your entry and try again.<br />";
	}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ballot</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/vnd.microsoft.icon" />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
	<style>
		html,
		body {
		  height: 100%;
		}

		body {
		  display: flex;
		  align-items: center;
		  padding-top: 40px;
		  padding-bottom: 40px;
		  background-color: #f5f5f5;
		}

		.form-signin {
		  width: 100%;
		  max-width: 330px;
		  padding: 15px;
		  margin: auto;
		}

		.form-signin .checkbox {
		  font-weight: 400;
		}

		.form-signin .form-floating:focus-within {
		  z-index: 2;
		}

		.form-signin input[type="email"] {
		  margin-bottom: -1px;
		  border-bottom-right-radius: 0;
		  border-bottom-left-radius: 0;
		}

		.form-signin input[type="password"] {
		  margin-bottom: 10px;
		  border-top-left-radius: 0;
		  border-top-right-radius: 0;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="row">			
			<center>
				<h2><strong>2021 VAYD Virtual Convention</strong></h2>
				<h3><?php echo($ballot_name); ?></h3>
			</center>
		</div>
		<?php 	// If form not yet submitted or error code exists
				if (!isset($_POST['login_delegate']) || ($error_code != '')) {
				
					// If error code, print error
  					if ($error_code != '') {
    					echo '<div style="width: 100%; text-align: center;"><p style="font-weight: bold; color: red;">'.$error_code.'</p></div>';
  					}

					// Print code entry form 
		?>
			<div style="width: 100%; text-align: center;">
				<form class="form-signin" method="post" autocomplete="off">
					<input type="tel" class="form-control" id="delegate_code" name="delegate_code" minlength="10" maxlength="10" placeholder="Delegate ID" required><br/>
	        		<button type="submit" name="login_delegate" value="go" class="btn btn-primary">Access the ballot</button>
				</form>
			</div>
		<?php 	} else { 
				// If form submitted and code is good, print ballot
		?>
		<div class="row">
			<center>				
				<p>Delegation:<br/><strong><?php echo($delegation_name); ?></strong></p>
				<p>You can cast up to: <strong><?php echo($delegate_max_vote); ?> votes</strong></p>
				<div id="SubmittingVoteDialog"></div>
			</center>
		</div>
			<form class="form-signin" id="DelegateBallot" name="submit-to-google-sheet" autocomplete="off">
				<div class="mb-3">
				  <input name="ballot" type="hidden" value="<?php echo($ballot_id); ?>">
				  <input name="delegate_id" type="tel" class="form-control" placeholder="Delegate ID" value="<?php echo($delegate_code); ?>" readonly required>
				  </div>
				  <?php if($form_type == "absolutes") { ?>
					  <div class="mb-3">
					  <input id="spot01" name="spot_01" type="checkbox" onclick="disableButtons(1);" class="form-check-label" value="<?php echo($delegate_max_vote); ?>">
					  <label class="form-label">Yes</label>
					  </div>
					  <div class="mb-3">
					  <input id="spot02" name="spot_02" type="checkbox" onclick="disableButtons(2);" class="form-check-label" value="<?php echo($delegate_max_vote); ?>">
					  <label class="form-label">No</label>
					  </div>
					  <div class="mb-3">					  	
					  <input id="spot03" name="spot_03" type="checkbox" onclick="disableButtons(3);" class="form-check-label" value="<?php echo($delegate_max_vote); ?>">
					  <label class="form-label">Abstain</label>
					  </div>
				  <?php } else { ?>
					  <div class="mb-3">
					  <label class="form-label"><?php echo($candidate_01); ?></label>
					  <input name="spot_01" type="number" step="0.00001" min="0" class="form-control" placeholder="0.00000">
					  </div>
					  <div class="mb-3">
					  <label class="form-label"><?php echo($candidate_02); ?></label>
					  <input name="spot_02" type="number" step="0.00001" min="0" class="form-control" placeholder="0.00000">
					  </div>
					  <?php if($candidate_03) { ?>
					  <div class="mb-3">
					  <label class="form-label"><?php echo($candidate_03); ?></label>
					  <input name="spot_03" type="number" step="0.00001" min="0" class="form-control" placeholder="0.00000">
					  </div>
					  <?php } else { ?>
					  <input name="spot_03" type="hidden" value="">
					  <?php } ?>
				  <?php } ?>
				  <div class="mb-3">
				  <button type="submit" class="btn btn-primary">Send</button>
				</div>
			</form>
		<?php } ?>
	</div>
<?php if (isset($_POST['login_delegate']) && $found) { ?>
<script>
  const scriptURL = '<?php echo($delegation_scriptURL); ?>';
  const form = document.forms['submit-to-google-sheet'];

<?php if($form_type == "absolutes") { ?>
  function disableButtons(spotId){
  	var checkbox_01 = document.getElementById("spot01").checked;
  	var checkbox_02 = document.getElementById("spot02").checked;  	
  	var checkbox_03 = document.getElementById("spot03").checked;

  	switch(spotId)
  	{
  		case 1:
  			if(checkbox_01)
  			{
  				document.getElementById("spot02").disabled = true;
  				document.getElementById("spot03").disabled = true;
  			} else {
  				document.getElementById("spot02").disabled = false;
  				document.getElementById("spot03").disabled = false;
  			}
  		break;
  		case 2:
  			if(checkbox_02)
  			{
  				document.getElementById("spot01").disabled = true;
  				document.getElementById("spot03").disabled = true;
  			} else {
  				document.getElementById("spot01").disabled = false;
  				document.getElementById("spot03").disabled = false;
  			}
  		break;
  		case 3:
  			if(checkbox_03)
  			{
  				document.getElementById("spot01").disabled = true;
  				document.getElementById("spot02").disabled = true;
  			} else {
  				document.getElementById("spot01").disabled = false;
  				document.getElementById("spot02").disabled = false;
  			}
  		break;
  		default:
  	}
  };
<?php } ?>

  form.addEventListener('submit', e => {
    e.preventDefault();

    // Get maximum amount of votes possible for the delegate
	var totalDelegate = parseFloat(<?php echo($delegate_max_vote); ?>);
    <?php if($form_type == "absolutes") { ?>
    	if(document.getElementById("spot01").checked) {
    		var spot01 = <?php echo($delegate_max_vote); ?>;
    		var spot02 = '';
    		var spot03 = '';
    	};
  		if(document.getElementById("spot02").checked) {
  			var spot01 = '';
  			var spot02 = <?php echo($delegate_max_vote); ?>;
    		var spot03 = '';
    	};  	
  		if(document.getElementById("spot03").checked) {  			
  			var spot01 = '';
    		var spot02 = '';
    		var spot03 = <?php echo($delegate_max_vote); ?>;
    	};
    <?php } else { ?>
	    // Get form elements
	  	var voteForm = document.getElementById("DelegateBallot").elements;
	  	// Get element values
	  	var spot01 = voteForm["spot_01"].value;
	  	var spot02 = voteForm["spot_02"].value;
	  	var spot03 = voteForm["spot_03"].value;
	<?php } ?>
	  	// Parse numbers as float or 0 if field is empty
	  	var spot_01 = parseFloat(spot01 ? spot01 : 0);
	  	var spot_02 = parseFloat(spot02 ? spot02 : 0);
	  	var spot_03 = parseFloat(spot03 ? spot03 : 0);
	  	// Add the values
	  	var voteTally = spot_01 + spot_02 + spot_03;  	
  	// Verify that vote tally doesn't exceed the maximum amount of votes for the delegate
  	if(voteTally > totalDelegate)
  	{
  		alert("Voting amount exceeds allocated votes! Please correct your votes.");
  		return false;
  	} else {
  		var submitWaitText = document.getElementById('SubmittingVoteDialog');
  		submitWaitText.innerHTML = "<div style=\"color: red;\"><strong>Submitting vote: please wait...</strong></div>";
	    fetch(scriptURL, { method: 'POST', body: new FormData(form)})
	      .then(response => alert("Your vote has been succesfully submitted!", submitWaitText.innerHTML = "<div style=\"color: green;\">Your vote has been submitted</div>"))
	      .catch(error => console.error('Error!', error.message))
  	}
  })
</script>
<?php } ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
</body>
</html>