<?php
include "./scripts/connection.php";

$total_doc_numberErr = $project_entityErr = $project_yearErr = $project_numberErr = $doc_disciplineErr = $doc_typeErr = $doc_numberErr = $doc_nameErr = $client_doc_numberErr = $descriptionErr = "";

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

$query1 = "SELECT * FROM disciplines";
$doc_discipline_options = performQuery($query1);
$doc_discipline = "EN";

$query2 = "SELECT * FROM document_types";
$doc_type_options = performQuery($query2);
$doc_type = "RP";

for ($i = 1; $i<10000; $i++) {$doc_number_options[$i] = sprintf('%04d',$i);};
$doc_number_options[0] = 'N/A';
ksort($doc_number_options);
$doc_number = "0001";

$doc_name = "";
$client_doc_number = "";
$description = "Enter a more elaborate document description here";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["project_entity"]))        {$total_doc_numberErr     .= "Entity is required. ";}           else {$project_entity      = test_input($_POST["project_entity"]);}
    if (empty($_POST["project_year"]))          {$total_doc_numberErr     .= "Project year is required. ";}     else {$project_year        = test_input($_POST["project_year"]);}
    if (empty($_POST["project_number"]))        {$total_doc_numberErr     .= "project number is required. ";}   else {$project_number      = test_input($_POST["project_number"]);}
    if (empty($_POST["doc_discipline"]))        {$total_doc_numberErr     .= "doc_discipline is required. ";}   else {$doc_discipline      = test_input($_POST["doc_discipline"]);}
    if (empty($_POST["doc_type"]))              {$total_doc_numberErr     .= "doc_type is required. ";}         else {$doc_type            = test_input($_POST["doc_type"]);}
    if (empty($_POST["doc_number"]))            {$total_doc_numberErr     .= "doc_number is required.";}        else {$doc_number          = test_input($_POST["doc_number"]);}
    if (empty($_POST["doc_name"]))              {$doc_nameErr             .= "doc_name is required";}           else {$doc_name            = test_input($_POST["doc_name"]);}
    $client_doc_number   = test_input($_POST["client_doc_number"]);
    $description         = test_input($_POST["description"]);

    //Check if GEN doc is properly defined
    if ($_POST["project_entity"] === "GEN" and $_POST["project_year"] != "N/A" and $_POST["project_number"] != "N/A")    {$total_doc_numberErr     .= "If Project = GEN, year and projectnumber should be N/A.";}

    //Check if Project number_format exists
    $query3 = "SELECT * FROM projects WHERE project_entity = '{$project_entity}' and project_year = '{$project_year}' and project_number = '{$project_number}'";
    $project_exists = performQuery($query3);
    if (!is_array($project_exists) and !is_object($project_exists)) {$total_doc_numberErr .= "Project Number does not exist. ";};

    //Check if projectnumber is unique
    $query4 = "SELECT * FROM documents WHERE project_entity = '{$project_entity}' and project_year = '{$project_year}' and project_number = '{$project_number}' and doc_discipline = '{$doc_discipline}' and doc_type = '{$doc_type}' and doc_number = '{$doc_number}'";
    $doc_exists = performQuery($query4);
    if (is_array($doc_exists) or is_object($doc_exists)) {
        foreach((array) $doc_exists as $i) {
            $total_doc_numberErr .= "Document Number {$i['project_entity']}{$i['project_year']}-{$i['project_number']}-{$i['doc_discipline']}-{$i['doc_type']}-{$i['doc_number']} already exists. ";
        };
    };

        // Insert into database
    if($total_doc_numberErr == "" and $doc_nameErr == "") {
        $query5 = "INSERT INTO documents (
                                project_entity,
                                project_year,
                                project_number,
                                doc_discipline,
                                doc_type,
                                doc_number,
                                doc_name,
                                client_doc_number,
                                description)
                    VALUES (    '{$project_entity}',
                                '{$project_year}',
                                '{$project_number}',
                                '{$doc_discipline}',
                                '{$doc_type}',
                                '{$doc_number}',
                                '{$doc_name}',
                                '{$client_doc_number}',
                                '{$description}');";
        performQueryINSERT($query5);
        header("Location: add_doc_success.php");
    };
};

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$query1 = "SELECT * FROM disciplines";
$disciplines = performQuery($query1);
$query2 = "SELECT * FROM document_types";
$doctypes = performQuery($query2);

?>
<div id="add_doc">
    <h2>Add New Document</h2>
    <p><span class="error">* required field.</span></p>
    <form id="add_doc" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
        Document Name:          <select name="project_entity"><?php foreach($project_entity_options as $opt) {echo '<option value="'.$opt.'" '.(($project_entity == $opt) ? 'selected' : "").'>'.$opt.'</option>';} ?>
        </select>
        <select name="project_year" <?php if($project_year_disabled) {echo 'disabled="disabled"';}; ?>><?php foreach($project_year_options as $opt) {echo '<option value="'.$opt.'" '.(($project_year == $opt) ? 'selected' : "").'>'.$opt.'</option>';} ?>
        </select>
        -
        <select name="project_number" <?php if($project_number_disabled) {echo 'disabled="disabled"';}; ?>><?php foreach($project_number_options as $opt) {echo '<option value="'.$opt.'" '.(($project_number == $opt) ? 'selected' : "").'>'.$opt.'</option>';} ?>
        </select>
        -
        <select name="doc_discipline"><?php foreach($doc_discipline_options as $opt) {echo '<option value="'.$opt['discipline_short'].'" '.(($doc_discipline == $opt['discipline_short']) ? 'selected' : "").'>'.$opt['discipline_short'].'</option>';} ?>
        </select>
        -
        <select name="doc_type"><?php foreach($doc_type_options as $opt) {echo '<option value="'.$opt['doc_type_short'].'" '.(($doc_type == $opt['doc_type_short']) ? 'selected' : "").'>'.$opt['doc_type_short'].'</option>';} ?>
        </select>
        -
        <select name="doc_number"><?php foreach($doc_number_options as $opt) {echo '<option value="'.$opt.'" '.(($doc_number == $opt) ? 'selected' : "").'>'.$opt.'</option>';} ?>
        </select>
        <span class="error">* <?php echo $total_doc_numberErr;?></span>
        <br><br>
        Document Name:          <input name="doc_name" type="text" size="45" maxlength="45" <?php echo 'value="'.$doc_name.'"'; ?>>
        <span class="error">* <?php echo $doc_nameErr;?></span>
        <br><br>
        Client document number: <input name="client_doc_number" type="text" size="45" <?php echo 'value="'.$client_doc_number.'"'; ?>>
        <span class="error"> <?php echo $client_doc_numberErr;?></span>
        <br><br>
        Description:            <textarea name="description" cols="40" rows="5"><?php echo $description; ?></textarea>
        <span class="error"> <?php echo $descriptionErr;?></span>
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
