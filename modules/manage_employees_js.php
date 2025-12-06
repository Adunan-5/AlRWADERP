<script>
    
    var purchase;
    
    <?php if(isset($body_value)){ ?>

      var dataObject = <?php echo new_html_entity_decode($body_value) ; ?>;
    <?php }?>

    // Helper to fetch projects for a staff (used to populate project select)
    function loadStaffProjects(staffId) {
      return $.getJSON(admin_url + 'hr_payroll/get_staff_projects/' + staffId);
    }

    // Custom renderer for Payslip button
    Handsontable.renderers.registerRenderer('payslipRenderer', function (instance, td, row, col, prop, value, cellProperties) {
        td.innerHTML = '';
        var buttonPreview = document.createElement('button');
        buttonPreview.type = 'button';   // 🔑 prevent form submission
        buttonPreview.className = 'btn btn-sm btn-info mr-1';
        buttonPreview.innerHTML = '<i class="fa fa-eye"></i>';
        buttonPreview.title = 'Preview';

        var buttonDL = document.createElement('button');
        buttonDL.type = 'button';   // 🔑 prevent form submission
        buttonDL.className = 'btn btn-sm btn-primary';
        buttonDL.innerHTML = '<i class="fa fa-download"></i>';

        var rowData = instance.getSourceDataAtRow(row);

        buttonPreview.addEventListener('click', function() {
          var staff = rowData.staff_id;
          var month = $('#month_employees').val();
          if (!staff || !month) return alert('Missing staff or month');
          // call preview endpoint
          $.get(admin_url + 'hr_payroll/get_preview/' + staff + '/' + month, function(resp) {
            resp = JSON.parse(resp);
            if (resp.status === 'ok') {
                $('#payslipPreviewBody').html(resp.html);
                $('#payslipPreviewModal').modal('show');
            } else {
                alert(resp.message || 'Preview error');
            }
          });
        });

        buttonDL.addEventListener('click', function() {
          var staff = rowData.staff_id;
          var month = $('#month_employees').val();
          window.open(admin_url + 'hr_payroll/generate_payslip/' + staff + '/' + month, '_blank');
        });

        td.appendChild(buttonPreview);
        td.appendChild(buttonDL);
    });

  // Open adjustment modal from preview or grid
  $(document).on('click', '#openAdjustmentModalPreview', function() {
      var staff = $(this).data('staff');
      var month = $(this).data('month');

      $('#adj_staff_id').val(staff);
      $('#adj_month').val(month);

      // clear and repopulate project select
      $('#adj_project_id').empty();

      loadStaffProjects(staff).done(function(data) {
          if (data && data.length > 0) {
              $.each(data, function(i, p) {
                  $('#adj_project_id').append(
                      $('<option>', { value: p.id, text: p.name })
                  );
              });
          }
      });

      // hide Payslip preview while showing adjustment
      $('#payslipPreviewModal').modal('hide');
      $('#adjustmentModal').modal('show');
      // default button label
      $('#adj_submit_btn').text('Add');
      $('#adjustmentModal').modal('show');
  });

  // When closing Adjustment modal, reopen Payslip preview
  $('#adjustmentModal').on('hidden.bs.modal', function () {
      var staff = $('#adj_staff_id').val();
      var month = $('#adj_month').val();
      if(staff && month) {
          $('#payslipPreviewModal').modal('show');
      }
  });

  // Change button label when type changes
  $('#adj_type').on('change', function() {
      var type = $(this).val();
      if (type === 'deduction') {
          $('#adj_submit_btn').text('Deduct');
      } else {
          $('#adj_submit_btn').text('Add');
      }
  });

  // submit adjustment
  $('#adjustmentForm').on('submit', function(e) {
    e.preventDefault();

    var payload = $(this).serialize();
    payload += '&' + csrfData.token_name + '=' + csrfData.hash; // add CSRF

    $.post(admin_url + 'hr_payroll/add_adjustment', payload, function(resp) {
        resp = JSON.parse(resp);
        if (resp.status === 'ok') {
            alert_float('success','Saved');
            $('#adjustmentModal').modal('hide');
            // 🔄 reload page to reflect changes everywhere
            setTimeout(function() {
                location.reload();
            }, 800);
        } else {
            alert_float('danger','Error');
        }
        // refresh CSRF hash
        csrfData.hash = resp.csrf_hash;
    });
});

  // Open payment modal
  $(document).on('click', '#openPaymentModalPreview', function() {
      var staff = $(this).data('staff');
      var month = $(this).data('month');
      $('#pay_staff_id').val(staff);
      $('#pay_month').val(month);

      // hide Payslip preview while showing payment
      $('#payslipPreviewModal').modal('hide');

      // fetch preview summary to get amount payable
      $.get(admin_url + 'hr_payroll/get_preview/' + staff + '/' + month, function(resp) {
        resp = JSON.parse(resp);
        if (resp.status === 'ok') {
            // parse total payable from returned HTML? Better: create a small endpoint that returns numeric payable.
            // For brevity, extract number from HTML with regex (quick & dirty)
            var m = resp.html.match(/To be paid[^\d]*([\d,]+\.\d{2})/);
            if (m && m[1]) $('#pay_amount').val(m[1].replace(/,/g,''));
        }
      });

      $('#paymentModal').modal('show');
  });

  // When closing Payment modal, reopen Payslip preview
  $('#paymentModal').on('hidden.bs.modal', function () {
      var staff = $('#pay_staff_id').val();
      var month = $('#pay_month').val();
      if(staff && month) {
          $('#payslipPreviewModal').modal('show');
      }
  });

  $('#paymentForm').on('submit', function(e) {
      e.preventDefault();

      var payload = $(this).serialize();
      payload += '&' + csrfData.token_name + '=' + csrfData.hash; // add CSRF

      $.post(admin_url + 'hr_payroll/make_payment', payload, function(resp) {
          resp = JSON.parse(resp);
          if (resp.status === 'ok') {
            alert_float('success','Payment recorded');
            $('#paymentModal').modal('hide');
            // 🔄 reload page to reflect changes everywhere
              setTimeout(function() {
                  location.reload();
              }, 800);
          } else {
            alert_float('danger','Payment failed');
          }
          // refresh CSRF hash
          csrfData.hash = resp.csrf_hash;
      });
  });

  $(document).on('click', '#sendPayslipEmail', function() {
    var $button = $(this); // Store reference to the button
    var staffId = $button.data('staff');
    var month = $button.data('month');

    // Disable the button and show a loading state
    $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');

    $.post(admin_url + 'hr_payroll/send_payslip_email', {
        staff_id: staffId,
        month: month
    }, function(response) {
        var res = JSON.parse(response);
        if (res.status === 'ok') {
            alert_float('success', 'Payslip sent successfully to staff email.');
        } else {
            alert_float('danger', res.message || 'Failed to send payslip.');
        }
    }).always(function() {
        // Re-enable the button and restore original text
        $button.prop('disabled', false).html('Email Payslip');
    });
});

  var hotElement1 = document.querySelector('#hrp_employees_value');
   var purchase = new Handsontable(hotElement1, {

    contextMenu: true,
    manualRowMove: true,
    manualColumnMove: true,
    stretchH: 'none',
    autoWrapRow: true,

    rowHeights: 20,
    defaultRowHeight: 10,
    minHeight:'100%',
    // width: '100%',
    // height:840,
    width: '100%',      // allow wrapper to control width
    height: '80vh',     // let container scroll, not fixed pixels

    licenseKey: 'non-commercial-and-evaluation',
    rowHeaders: true,
    autoColumnsub_group: {
      samplingRatio: 23
    },
    dropdownMenu: true,
     hiddenColumns: {
        columns: [0,1,2],
        indicators: false
      },
    multiColumnSorting: {
        indicator: true
      }, 
    fixedColumnsLeft: 5,

    filters: true,
    manualRowResub_group: true,
    manualColumnResub_group: true,
    allowInsertRow: false,
    allowRemoveRow: false,
    columnHeaderHeight: 40,

    rowHeights: 40,
    rowHeaderWidth: [44],


    columns: <?php echo new_html_entity_decode($columns) ?>,

    colHeaders: <?php echo new_html_entity_decode($col_header); ?>,

    data: dataObject,

    // Add afterChange hook for automatic calculations
    afterChange: function(changes, source) {
        'use strict';
        if (source === 'edit') {
            changes.forEach(function(change) {
                var row = change[0];
                var prop = change[1];
                var newValue = change[3];

                // Get column indices for relevant fields
                var colIndices = {};
                purchase.getSettings().columns.forEach(function(col, index) {
                    colIndices[col.data] = index;
                });

                // List of fields that trigger calculations
                var triggerFields = [
                    'gosi_basic_salary', 'gosi_housing_allowance', 'gosi_other_allowance', 'gosi_deduction',
                    'basic', 'ot_hours', 'ot_rate', 'allowance'
                ];

                // Only proceed if the changed field is one of the input fields
                if (triggerFields.includes(prop)) {
                    // Get values for calculations (convert to numbers, default to 0 if invalid)
                    var gosi_basic_salary = parseFloat(purchase.getDataAtCell(row, colIndices['gosi_basic_salary']) || 0);
                    var gosi_housing_allowance = parseFloat(purchase.getDataAtCell(row, colIndices['gosi_housing_allowance']) || 0);
                    var gosi_other_allowance = parseFloat(purchase.getDataAtCell(row, colIndices['gosi_other_allowance']) || 0);
                    var gosi_deduction = parseFloat(purchase.getDataAtCell(row, colIndices['gosi_deduction']) || 0);
                    var deduction = parseFloat(purchase.getDataAtCell(row, colIndices['deduction']) || 0);
                    var basic = parseFloat(purchase.getDataAtCell(row, colIndices['basic']) || 0);
                    var ot_hours = parseFloat(purchase.getDataAtCell(row, colIndices['ot_hours']) || 0);
                    var ot_rate = parseFloat(purchase.getDataAtCell(row, colIndices['ot_rate']) || 0);
                    var allowance = parseFloat(purchase.getDataAtCell(row, colIndices['allowance']) || 0);

                    // Calculate total_amount
                    var total_amount = gosi_basic_salary + gosi_housing_allowance + gosi_other_allowance - gosi_deduction;
                    purchase.setDataAtCell(row, colIndices['total_amount'], total_amount.toFixed(2), 'auto');

                    // Calculate ot_amount
                    var ot_amount = ot_hours * ot_rate;
                    purchase.setDataAtCell(row, colIndices['ot_amount'], ot_amount.toFixed(2), 'auto');

                    // Calculate full_salary
                    var full_salary = basic + ot_amount + allowance - deduction;
                    purchase.setDataAtCell(row, colIndices['full_salary'], full_salary.toFixed(2), 'auto');

                    // Calculate balance
                    var balance = full_salary - basic;
                    purchase.setDataAtCell(row, colIndices['balance'], balance.toFixed(2), 'auto');
                }
            });
        }
    }

  });

    //filter
  function employees_filter (invoker){
    'use strict';

    var data = {};
    data.month = $("#month_employees").val();
    data.staff  = $('select[name="staff_employees[]"]').val();
    data.department = $('#department_employees').val();
    data.role_attendance = $('select[name="role_employees[]"]').val();

    // Log filter data for debugging
    console.log('Filter Data:', data);

    $.post(admin_url + 'hr_payroll/employees_filter', data).done(function(response) {
      console.log(response);
      response = JSON.parse(response);
      dataObject = response.data_object;
      purchase.updateSettings({
        data: dataObject,

      })
      $('input[name="month"]').val(response.month);
      $('.save_manage_employees').html(response.button_name);
      
    });
  };



  var purchase_value = purchase;

  $('.hrp_employees_synchronization').on('click', function() {
    'use strict';

    var valid_contract = $('#hrp_employees_value').find('.htInvalid').html();

    if(valid_contract){
      alert_float('danger', "<?php echo _l('data_invalid') ; ?>");
    }else{

      $('input[name="hrp_employees_value"]').val(JSON.stringify(purchase_value.getData()));   
      $('input[name="employees_fill_month"]').val($("#month_employees").val());
      $('input[name="hrp_employees_rel_type"]').val('synchronization');   
      $('#add_manage_employees').submit(); 

    }
  });


  $('.save_manage_employees').on('click', function() {
    'use strict';

    var valid_contract = $('#hrp_employees_value').find('.htInvalid').html();

    if(valid_contract){
      alert_float('danger', "<?php echo _l('data_invalid') ; ?>");
    }else{

      $('input[name="hrp_employees_value"]').val(JSON.stringify(purchase_value.getData()));   
      $('input[name="employees_fill_month"]').val($("#month_employees").val());
      $('input[name="hrp_employees_rel_type"]').val('update');   
      $('#add_manage_employees').submit(); 

    }
  });

  $('#department_employees').on('change', function() {
    'use strict';

    $('input[name="department_employees_filter"]').val($("#department_employees").val());   
    employees_filter();
  });

  $('#staff_employees').on('change', function() {
    'use strict';

    $('input[name="staff_employees_filter"]').val($("#staff_employees").val());   
    employees_filter();
  });

  $('#role_employees').on('change', function() {
    'use strict';
    
    $('input[name="role_employees_filter"]').val($("#role_employees").val());   
    employees_filter();
  });
  
  $('#month_employees').on('change', function() {
    'use strict';

    employees_filter();

  });

  $('.hrp_employees_copy').on('click', function() {
    'use strict';

    var data = {};
    data.month = $("#month_employees").val();

    $.post(admin_url + 'hr_payroll/employees_copy', data).done(function(response) {
      response = JSON.parse(response);

      alert_float(response.status, response.message);
      employees_filter();
      
    });

  });

  function payslipRenderer(instance, td, row, col, prop, value, cellProperties) {
      td.innerHTML = '<button class="btn btn-sm btn-primary generate-payslip" data-row="'+row+'">' +
                    '<i class="fa fa-download"></i> Payslip</button>';
      td.style.textAlign = 'center';
      return td;
  }

  $(document).on('click', '.generate-payslip', function() {
    var row = $(this).data('row');
    var rowData = purchase.getSourceDataAtRow(row); // hot = Handsontable instance

    $.ajax({
        url: admin_url + 'hr_payroll/generate_payslip_pdf',
        type: 'POST',
        data: { staff_id: rowData.staff_id },
        xhrFields: { responseType: 'blob' }, // for file download
        success: function(blob) {
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = "payslip_" + rowData.staff_id + ".pdf";
            link.click();
        }
    });
});



</script>