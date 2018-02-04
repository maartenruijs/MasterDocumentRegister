<?php

$total_project_numberErr = $project_entityErr = $project_yearErr = $project_numberErr = $clientErr = $short_descriptionErr = $statusErr = $long_descriptionErr = "";

$project_entity_options = array( 'ARC', 'OI', 'INT', 'GEN');
$project_entity = "OI";

foreach(range(2013, 2022) as $i) {$project_year_options[$i] = (string) $i;};
$project_year_options[0] = 'N/A';
ksort($project_year_options);
$project_year = "2018";
$project_year_disabled = false;

for ($i = 1; $i<1000; $i++) {$project_number_options[$i] = sprintf('%03d',$i);};
$project_number_options[0] = 'N/A';
ksort($project_number_options);
$project_number = "001";
$project_number_disabled = false;

$client = "";

$short_description = "";

$status_options = array( 'Lead', 'Proposal', 'Active', 'Archived');
$status = "Active";

$long_description = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["project_entity"]))        {$total_project_numberErr   .= "Entity is required. ";}           else {$project_entity     = test_input($_POST["project_entity"]);}
    if (empty($_POST["project_year"]))          {$total_project_numberErr   .= "Project year is required. ";}     else {$project_year       = test_input($_POST["project_year"]);}
    if (empty($_POST["project_number"]))        {$total_project_numberErr   .= "Project number is required. ";}   else {$project_number     = test_input($_POST["project_number"]);}
    if (empty($_POST["client"]))                {$clientErr             .= "client is required. ";}           else {$client             = test_input($_POST["client"]);}
    if (empty($_POST["short_description"]))     {$short_descriptionErr  .= "Short_description is required. ";}else {$short_description  = test_input($_POST["short_description"]);}
    if (empty($_POST["status"]))                {$statusErr             .= "Status is required.";}            else {$status             = test_input($_POST["status"]);}
    $long_description = test_input($_POST["long_description"]);

    //Check if GEN doc is properly defined
    if ($_POST["project_entity"] === "GEN" and $_POST["project_year"] != "N/A" and $_POST["project_number"] != "N/A")    {$total_project_numberErr     .= "If Project = GEN, year and projectnumber should be N/A.";};

    //Check if projectnumber is unique
    $query1 = "SELECT * FROM projects WHERE project_entity = '{$project_entity}' and project_year = '{$project_year}' and project_number = '{$project_number}'";
    $project_exists = performQuery($query1);
    if (is_array($project_exists) or is_object($project_exists)) {
        foreach((array) $project_exists as $i) {
            $total_project_numberErr .= "Project {$i['project_entity']}{$i['project_year']}-{$i['project_number']} already exists. ";
        };
    };

        // Insert into database
    if($total_project_numberErr == "" and $doc_nameErr == "") {
        $query2 = "INSERT INTO projects (
                                project_entity,
                                project_year,
                                project_number,
                                client,
                                short_description,
                                status,
                                long_description)
                    VALUES (    '{$project_entity}',
                                '{$project_year}',
                                '{$project_number}',
                                '{$client}',
                                '{$short_description}',
                                '{$status}',
                                '{$long_description}');";
        performQueryINSERT($query2);
        header("Location: add_project_success.php");
    };
};

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

?>
<div id="add_project">
    <h2>Add New Project</h2>
    <p><span class="error">* required field.</span></p>
    <form id="add_doc" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
        Project Number:          <select name="project_entity"><?php foreach($project_entity_options as $opt) {echo '<option value="'.$opt.'" '.(($project_entity == $opt) ? 'selected' : "").'>'.$opt.'</option>';} ?>
        </select>
        <select name="project_year" <?php if($project_year_disabled) {echo 'disabled="disabled"';}; ?>><?php foreach($project_year_options as $opt) {echo '<option value="'.$opt.'" '.(($project_year == $opt) ? 'selected' : "").'>'.$opt.'</option>';} ?>
        </select>
        -
        <select name="project_number" <?php if($project_number_disabled) {echo 'disabled="disabled"';}; ?>><?php foreach($project_number_options as $opt) {echo '<option value="'.$opt.'" '.(($project_number == $opt) ? 'selected' : "").'>'.$opt.'</option>';} ?>
        </select>
        <span class="error">
            * <?php echo $total_project_numberErr;?>
        </span>
        <br><br>
        Client:
        <input name="client" type="text" size="45" maxlength="45" value="<?php echo $client; ?>" />
        <span class="error">*
            <?php echo $clientErr;?>
        </span>
        <br><br>
        Short description:          
        <input name="short_description" type="text" size="45" maxlength="45" value="<?php echo $short_description; ?>">
        <span class="error">* <?php echo $short_descriptionErr;?></span>
        <br><br>
        Status:
        <select name="status"><?php foreach($status_options as $opt) {echo '<option value="'.$opt.'" '.(($status == $opt) ? 'selected' : "").'>'.$opt.'</option>';} ?>
        </select>
        <span class="error">* <?php echo $statusErr;?></span>
        <br><br>
        Long description:            <textarea name="long_description" cols="40" rows="5"><?php echo $long_description; ?></textarea>
        <span class="error"> <?php echo $long_descriptionErr;?></span>
        <br><br>
        <input type="submit" value="Submit">
        <a href="javascript:history.go(-1)">Cancel</a>
        <br><br>
    </form>
    <br><br>

    <script>
        changeEntity();
        EnableBeforeSubmit();
        disableOnInitialization();
    </script>
</div>
