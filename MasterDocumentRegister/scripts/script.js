//Function to open accordion
function openAccordion(el) {
    $(el).toggleClass("accordion_active");
    $(el).toggleClass("accordion");
    while ($(el).next().is('.panel,.panel_active')) {
        $(el).next().toggleClass("panel_active");
        $(el).next().toggleClass("panel");
        el = $(el).next();
    }
};

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
};

function EnableBeforeSubmit() {
    document.getElementById('add_doc').addEventListener('submit', function () {
        for (var i = 0; i < this.children.length; i++) {
            var child = this.children[i];
            if (child.disabled) {
                child.disabled = false;
            }
        }
    });
};

function disableOnInitialization() {
    $(document).ready(function () {
        if ($('[name="project_entity"]').val() == "GEN") {
            if ($('[name="project_year"]').val() == "N/A") { $('[name="project_year"]').prop('disabled', true); };
            if ($('[name="project_number"]').val() == "N/A") { $('[name="project_number"]').prop('disabled', true); };
        };
    });
};

// Increase pagenumber by 1
function nextPage(pagenum, max_pagenum) {
    if ((pagenum + 1) > max_pagenum) {
        nextpage = pagenum;
    } else {
        nextpage = pagenum + 1;
    };
    document.getElementById('sel_pagenum').value = nextpage;
};

// Goto last page
function LastPage(max_pagenum) {
    nextpage = max_pagenum;
    document.getElementById('sel_pagenum').value = nextpage;
};

// Decrease pagenumber by 1
function prevPage(pagenum) {
    if ((pagenum - 1) < 1) {
        nextpage = pagenum;
    } else {
        nextpage = pagenum - 1;
    };
    document.getElementById('sel_pagenum').value = nextpage;
};

// Goto first page
function firstPage() {
        nextpage = 1;
    document.getElementById('sel_pagenum').value = nextpage;
};

function modify_order(inp_id) {
	if (document.getElementById(inp_id).value == 'none') {
		document.getElementById(inp_id).value = 'asc';
	} else if (document.getElementById(inp_id).value == 'asc') {
		document.getElementById(inp_id).value = 'desc';
	} else if (document.getElementById(inp_id).value == 'desc') {
		document.getElementById(inp_id).value = 'none';
	};
};

function modify_order_of_orderinput(num_id1, num_id2, inp_id2) {
	if (document.getElementById(num_id2).value != ""){
		document.getElementById(num_id2).value = parseInt(document.getElementById(num_id2).value, 10) + 1;
	};
	if (num_id1 == num_id2) {
		if (document.getElementById(inp_id2).value == 'none') {
			document.getElementById(num_id2).value = "";
		} else {
			document.getElementById(num_id2).value = 1;
		}; 
	};
};

