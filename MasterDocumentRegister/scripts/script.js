//Function to open tabs
/*function openTab(evt, tabName) {
    // Declare all variables
    var i, tabcontent, tablinks;

    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }

    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }

    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(tabName).style.display = "block";
    evt.currentTarget.className += " active";
} */

function openAccordion(el) {
    $(el).toggleClass("accordion_active");
    $(el).toggleClass("accordion");
    while ($(el).next().is('.panel,.panel_active')) {
        $(el).next().toggleClass("panel_active");
        $(el).next().toggleClass("panel");
        el = $(el).next();
    }
}


function changeEntity() {
    $(document).on('change', '[name="project_entity"]', function () {
        if ($('[name="project_entity"]').val() == "GEN") {
            $('[name="project_year"]').val("N/A");
            $('[name="project_number"]').val("N/A");
            $('[name="project_year"]').prop('disabled', true);
            $('[name="project_number"]').prop('disabled', true);
        } else {
            $('[name="project_year"]').prop('disabled', false);
            $('[name="project_number"]').prop('disabled', false);
        };
    });
}

function EnableBeforeSubmit() {
    document.getElementById('add_doc').addEventListener('submit', function () {
        for (var i = 0; i < this.children.length; i++) {
            var child = this.children[i];
            if (child.disabled) {
                child.disabled = false;
            }
        }
    });
}

function disableOnInitialization() {
    $(document).ready(function () {
        if ($('[name="project_entity"]').val() == "GEN") {
            if ($('[name="project_year"]').val() == "N/A") { $('[name="project_year"]').prop('disabled', true); };
            if ($('[name="project_number"]').val() == "N/A") { $('[name="project_number"]').prop('disabled', true); };
        };
    });
}

function nextPage(pagenum, max_pagenum) {
    if ((pagenum + 1) > max_pagenum) {
        nextpage = pagenum;
    } else {
        nextpage = pagenum + 1;
    };
    document.getElementById('sel_pagenum').value = nextpage;
}

function LastPage(max_pagenum) {
    nextpage = max_pagenum;
    document.getElementById('sel_pagenum').value = nextpage;
}

function prevPage(pagenum) {
    if ((pagenum - 1) < 1) {
        nextpage = pagenum;
    } else {
        nextpage = pagenum - 1;
    };
    document.getElementById('sel_pagenum').value = nextpage;
}

function firstPage() {
        nextpage = 1;
    document.getElementById('sel_pagenum').value = nextpage;
}

