<script>
    
    var purchase;

    // Helper to get URL query param by name (e.g., 'company' from ?company=mohtarifeen)
  function getParameterByName(name, url) {
      if (!url) url = window.location.href;
      name = name.replace(/[\[\]]/g, '\\$&');
      var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
          results = regex.exec(url);
      if (!results) return null;
      if (!results[2]) return '';
      return decodeURIComponent(results[2].replace(/\+/g, ' '));
  }
    
    <?php if(isset($body_value)){ ?>

      var dataObject = <?php echo new_html_entity_decode($body_value) ; ?>;
      console.log('Initial dataObject loaded with ' + dataObject.length + ' employees');

      <?php if (isset($is_payroll_mode) && $is_payroll_mode): ?>
        // Payroll mode: Use pre-filtered data from PHP, don't call AJAX
        var isPayrollMode = true;
        console.log('Payroll Mode: Using pre-filtered employees from payroll_id=<?php echo isset($payroll) ? $payroll->id : 0; ?>');
      <?php else: ?>
        // Legacy mode: Call AJAX to filter employees
        var isPayrollMode = false;
        employees_filterInit();
      <?php endif; ?>
    <?php }?>

    // Helper to fetch projects for a staff (used to populate project select)
    function loadStaffProjects(staffId) {
      return $.getJSON(admin_url + 'hr_payroll/get_staff_projects/' + staffId);
    }

    function reloadPayslipPreview(staffId, month) {
      $.get(admin_url + 'hr_payroll/get_preview/' + staffId + '/' + month, function(resp) {
          resp = JSON.parse(resp);
          if (resp.status === 'ok') {
              $('#payslipPreviewBody').html(resp.html);
              $('#payslipPreviewModal').modal('show');
          } else {
              alert_float('danger', resp.message || 'Error reloading payslip preview');
          }
      });
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
//   $('#adjustmentForm').on('submit', function(e) {
//     e.preventDefault();

//     var payload = $(this).serialize();
//     payload += '&' + csrfData.token_name + '=' + csrfData.hash; // add CSRF

//     $.post(admin_url + 'hr_payroll/add_adjustment', payload, function(resp) {
//         resp = JSON.parse(resp);
//         if (resp.status === 'ok') {
//             alert_float('success','Saved');
//             $('#adjustmentModal').modal('hide');
//             // 🔄 reload page to reflect changes everywhere
//             setTimeout(function() {
//                 location.reload();
//             }, 800);
//         } else {
//             alert_float('danger','Error');
//         }
//         // refresh CSRF hash
//         csrfData.hash = resp.csrf_hash;
//     });
// });

$('#adjustmentForm').on('submit', function(e) {
    e.preventDefault();

    var payload = $(this).serialize();
    payload += '&' + csrfData.token_name + '=' + csrfData.hash; // add CSRF

    $.post(admin_url + 'hr_payroll/add_adjustment', payload, function(resp) {
        resp = JSON.parse(resp);
        if (resp.status === 'ok') {
            alert_float('success', 'Saved');

            var staff = $('#adj_staff_id').val();
            var month = $('#adj_month').val();

            $('#adjustmentModal').modal('hide');

            // 🔄 Instead of reloading page, reload payslip modal with updated data
            setTimeout(function() {
              reloadPayslipPreview(staff, month);
              // Only reload employees in non-payroll mode
              if (typeof isPayrollMode === 'undefined' || !isPayrollMode) {
                employees_filterInit();
              }
            }, 500);
        } else {
          // Only reload employees in non-payroll mode
          if (typeof isPayrollMode === 'undefined' || !isPayrollMode) {
            employees_filterInit();
          }
          alert_float('danger', 'Error');
        }

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

  // $('#paymentForm').on('submit', function(e) {
  //     e.preventDefault();

  //     var payload = $(this).serialize();
  //     payload += '&' + csrfData.token_name + '=' + csrfData.hash; // add CSRF

  //     $.post(admin_url + 'hr_payroll/make_payment', payload, function(resp) {
  //         resp = JSON.parse(resp);
  //         if (resp.status === 'ok') {
  //           alert_float('success','Payment recorded');
  //           $('#paymentModal').modal('hide');
  //           // 🔄 reload page to reflect changes everywhere
  //             setTimeout(function() {
  //                 location.reload();
  //             }, 800);
  //         } else {
  //           alert_float('danger','Payment failed');
  //         }
  //         // refresh CSRF hash
  //         csrfData.hash = resp.csrf_hash;
  //     });
  // });

  $('#paymentForm').on('submit', function(e) {
    e.preventDefault();

    var payload = $(this).serialize();
    payload += '&' + csrfData.token_name + '=' + csrfData.hash;

    $.post(admin_url + 'hr_payroll/make_payment', payload, function(resp) {
        resp = JSON.parse(resp);
        if (resp.status === 'ok') {
            alert_float('success','Payment recorded');

            var staff = $('#pay_staff_id').val();
            var month = $('#pay_month').val();

            $('#paymentModal').modal('hide');

            // 🔄 Reload payslip preview (fresh HTML)
            setTimeout(function() {
                reloadPayslipPreview(staff, month);
            }, 500);
        } else {
            alert_float('danger','Payment failed');
        }
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

$('#payslipPreviewBody').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Loading updated payslip...</div>');

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

                // Get column indices for relevant fields
                var colIndices = {};
                purchase.getSettings().columns.forEach(function(col, index) {
                    colIndices[col.data] = index;
                });

                // List of fields that trigger calculations
                var triggerFields = [
                    'gosi_basic_salary', 'gosi_housing_allowance', 'gosi_other_allowance', 'gosi_deduction',
                    'basic', 'ot_hours', 'ot_rate', 'allowance', 'additions', 'deduction'
                ];

                var prop = change[1];
                // Only proceed if the changed field is one of the input fields
                if (triggerFields.includes(prop)) {
                    calculateRow(row, colIndices);
                }
            });
        }
    }

  });

    // NEW: Extracted function for row calculations (used in afterChange and initial load)
    function calculateRow(row, colIndices = null) {
        if (colIndices === null) {
            // Build colIndices if not provided
            colIndices = {};
            purchase.getSettings().columns.forEach(function(col, index) {
                colIndices[col.data] = index;
            });
        }

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
        var additions = parseFloat(purchase.getDataAtCell(row, colIndices['additions']) || 0);

        // Calculate total_amount
        var total_amount = gosi_basic_salary + gosi_housing_allowance + gosi_other_allowance - gosi_deduction;
        purchase.setDataAtCell(row, colIndices['total_amount'], total_amount.toFixed(2), 'auto');

        // Calculate ot_amount
        var ot_amount = ot_hours * ot_rate;
        purchase.setDataAtCell(row, colIndices['ot_amount'], ot_amount.toFixed(2), 'auto');

        // Calculate full_salary: Basic + OT Amount + Allowance + Additions - Deduction
        var full_salary = basic + ot_amount + allowance + additions - deduction;
        purchase.setDataAtCell(row, colIndices['full_salary'], full_salary.toFixed(2), 'auto');

        // Calculate balance: Full Salary - Total Amount
        var balance = full_salary - total_amount;
        purchase.setDataAtCell(row, colIndices['balance'], balance.toFixed(2), 'auto');
    }

    // NEW: Trigger calculations for all rows after initial data load
    function triggerInitialCalculations() {
        var colIndices = {};
        purchase.getSettings().columns.forEach(function(col, index) {
            colIndices[col.data] = index;
        });

        purchase.batch(() => {
            for (var r = 0; r < purchase.countRows(); r++) {
                calculateRow(r, colIndices);
            }
        });
    }

    // Call after Handsontable init ONLY if NOT in payroll mode
    // In payroll mode, data comes from database and should not be recalculated
    if (typeof isPayrollMode === 'undefined' || !isPayrollMode) {
        setTimeout(triggerInitialCalculations, 100);
    } else {
        console.log('Payroll Mode: Skipping automatic recalculation, using database values');
    }

    //filter
  function employees_filter (invoker){
    'use strict';

    var data = {};
    data.month = $("#month_employees").val();
    data.staff  = $('select[name="staff_employees[]"]').val();
    data.department = $('#department_employees').val();
    data.role_attendance = $('select[name="role_employees[]"]').val();
    data.company = getParameterByName('company');

    // CRITICAL: Include payroll_id if in payroll mode to prevent loading all employees
    <?php if (isset($is_payroll_mode) && $is_payroll_mode && isset($payroll)): ?>
    data.payroll_id = <?php echo $payroll->id; ?>;
    <?php endif; ?>

    // Log filter data for debugging
    console.log('Filter Data:', data);

    // 🔹 Block the entire Handsontable area before AJAX starts
    var $tableArea = $('#hrp_employees_value').closest('.content'); 
    blockArea($tableArea);

    $.post(admin_url + 'hr_payroll/employees_filter', data).done(function(response) {
      console.log(response);
      response = JSON.parse(response);
      dataObject = response.data_object;
      purchase.updateSettings({
        data: dataObject,

      })
      $('input[name="month"]').val(response.month);
      $('.save_manage_employees').html(response.button_name);
      setTimeout(function() {
        adjustTableHeight();
        triggerInitialCalculations(); // NEW: Recalculate after filter load
      }, 100);
    })
    .always(function() {
      // 🔹 Unblock the area whether success or failure
      unBlockArea($tableArea);
    });
  };

  function employees_filterInit (invoker){
    'use strict';

    var data = {};
    data.month = $("#month_employees").val();
    data.staff  = $('select[name="staff_employees[]"]').val();
    data.department = $('#department_employees').val();
    data.role_attendance = $('select[name="role_employees[]"]').val();
    data.company = getParameterByName('company');

    // CRITICAL: Include payroll_id if in payroll mode to prevent loading all employees
    <?php if (isset($is_payroll_mode) && $is_payroll_mode && isset($payroll)): ?>
    data.payroll_id = <?php echo $payroll->id; ?>;
    <?php endif; ?>

    // Log filter data for debugging
    console.log('Filter Data:', data);

    // 🔹 Block the entire Handsontable area before AJAX starts
    var $tableArea = $('#hrp_employees_value').closest('.content'); 
    // blockArea($tableArea);

    $.post(admin_url + 'hr_payroll/employees_filter', data).done(function(response) {
      console.log(response);
      response = JSON.parse(response);
      dataObject = response.data_object;
      purchase.updateSettings({
        data: dataObject,

      })
      $('input[name="month"]').val(response.month);
      $('.save_manage_employees').html(response.button_name);
      setTimeout(function() {
        adjustTableHeight();
        triggerInitialCalculations(); // NEW: Recalculate after init load
      }, 100);
    })
    .always(function() {
      // 🔹 Unblock the area whether success or failure
      // unBlockArea($tableArea);
    });
  };

  function adjustTableHeight() {
    var container = $('#hrp_employees_value');
    var tableWrapper = container.find('.ht_master .wtHolder');
    
    if (tableWrapper.length) {
        // Calculate appropriate height based on row count
        var rowCount = purchase.countRows();
        var headerHeight = 40; // Approximate header height
        var rowHeight = 40; // Your row height
        var borderAndPadding = 20; // Account for borders and padding
        
        // Calculate total height needed
        var totalHeight = headerHeight + (rowCount * rowHeight) + borderAndPadding;
        
        // Set minimum and maximum height constraints
        var minHeight = 200; // Minimum table height
        var maxHeight = 800; // Maximum table height (or use '80vh')
        
        var finalHeight = Math.min(Math.max(totalHeight, minHeight), maxHeight);
        
        // Update Handsontable height
        purchase.updateSettings({
            height: finalHeight
        });
        
        console.log('Adjusted table height to:', finalHeight, 'for', rowCount, 'rows');
    }
}



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


  // $('.save_manage_employees').on('click', function() {
  //   'use strict';

  //   var valid_contract = $('#hrp_employees_value').find('.htInvalid').html();

  //   if(valid_contract){
  //     alert_float('danger', "<?php echo _l('data_invalid') ; ?>");
  //   }else{

  //     $('input[name="hrp_employees_value"]').val(JSON.stringify(purchase_value.getData()));   
  //     $('input[name="employees_fill_month"]').val($("#month_employees").val());
  //     $('input[name="hrp_employees_rel_type"]').val('update');   
  //     $('#add_manage_employees').submit(); 

  //   }
  // });

  $('.save_manage_employees').on('click', function(e) {
    e.preventDefault();
    'use strict';

    var valid_contract = $('#hrp_employees_value').find('.htInvalid').html();

    if (valid_contract) {
      alert_float('danger', "<?php echo _l('data_invalid'); ?>");
    } else {
      var formData = {
        hrp_employees_value: JSON.stringify(purchase_value.getData()),
        employees_fill_month: $("#month_employees").val(),
        hrp_employees_rel_type: 'update'
      };

      // Add CSRF token
      formData[csrfData.token_name] = csrfData.hash;

      // Add filter values to prevent deletion of non-visible employees when filters are active
      formData.department_employees_filter = $('#department_employees').val() || '';
      formData.staff_employees_filter = $('select[name="staff_employees[]"]').val() || '';
      formData.role_employees_filter = $('select[name="role_employees[]"]').val() || '';

      // Add payroll_id if in payroll mode
      <?php if (isset($is_payroll_mode) && $is_payroll_mode && isset($payroll)): ?>
      formData.payroll_id = <?php echo $payroll->id; ?>;
      <?php endif; ?>

      var $tableArea = $('#hrp_employees_value').closest('.content');
      blockArea($tableArea);

      $.ajax({
        url: admin_url + 'hr_payroll/add_manage_employees', // Controller function URL
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
          // Update CSRF token if returned
          if (response.csrf_hash) {
            csrfData.hash = response.csrf_hash;
          }

          if (response.success) {
            alert_float('success', "<?php echo _l('updated_successfully'); ?>", 5000);
          } else {
            alert_float('danger', response.message || "<?php echo _l('something_went_wrong'); ?>");
            console.error('Update failed:', response);
          }
        },
        error: function(xhr, status, error) {
          console.error('AJAX Error:', {
            status: xhr.status,
            statusText: xhr.statusText,
            responseText: xhr.responseText,
            error: error
          });

          var errorMsg = "<?php echo _l('error_occured'); ?>";

          // Try to parse error response
          try {
            var resp = JSON.parse(xhr.responseText);
            if (resp.message) {
              errorMsg = resp.message;
            }
          } catch(e) {
            // If not JSON, show status text
            if (xhr.statusText) {
              errorMsg += ': ' + xhr.statusText;
            }
          }

          alert_float('danger', errorMsg);
        },
        complete: function() {
          // 🔹 Always unblock after success or error
          unBlockArea($tableArea);
        }
      });
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

    // In payroll mode, month is fixed - prevent filtering
    if (typeof isPayrollMode !== 'undefined' && isPayrollMode) {
      console.log('Payroll Mode: Month change blocked');
      return false;
    }

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

// Select All checkbox functionality
$('#select_all_employees').on('change', function() {
    'use strict';
    var isChecked = $(this).is(':checked');
    var sourceData = purchase.getSourceData();

    // Update all rows' select field
    for (var i = 0; i < sourceData.length; i++) {
        sourceData[i].select = isChecked;
    }

    // Refresh the grid to show updated checkboxes
    purchase.render();
});

// Export selected employees to bank transfer file
$('.export_selected_employees').on('click', function() {
    'use strict';

    // Get all data from Handsontable
    var allData = purchase.getData();
    var sourceData = purchase.getSourceData();

    // Filter only selected rows (where checkbox is checked)
    var selectedStaffIds = [];
    for (var i = 0; i < sourceData.length; i++) {
        if (sourceData[i].select === true) {
            selectedStaffIds.push(sourceData[i].staff_id);
        }
    }

    // Check if any rows are selected
    if (selectedStaffIds.length === 0) {
        alert_float('warning', '<?php echo _l("Please select at least one employee to export"); ?>');
        return;
    }

    // Get current month
    var month = $('#month_employees').val();

    // Get company filter from URL
    var company = getParameterByName('company');

    // Prepare data
    var exportData = {
        staff_ids: selectedStaffIds,
        month: month,
        company: company
    };

    // Show loading
    var $button = $(this);
    var originalText = $button.html();
    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l("Exporting..."); ?>');

    // Send AJAX request to export
    $.ajax({
        url: admin_url + 'hr_payroll/export_payroll_bank_file',
        type: 'POST',
        data: exportData,
        xhrFields: {
            responseType: 'blob' // Important for file download
        },
        success: function(blob, status, xhr) {
            // Get filename from Content-Disposition header or use default
            var filename = 'payroll_export_' + month + '.xlsx';
            var disposition = xhr.getResponseHeader('Content-Disposition');
            if (disposition && disposition.indexOf('filename=') !== -1) {
                var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                var matches = filenameRegex.exec(disposition);
                if (matches != null && matches[1]) {
                    filename = matches[1].replace(/['"]/g, '');
                }
            }

            // Create download link
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            alert_float('success', '<?php echo _l("Export completed successfully"); ?>');
        },
        error: function(xhr) {
            // Try to parse error message from blob
            if (xhr.responseText) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    alert_float('danger', response.message || '<?php echo _l("Export failed"); ?>');
                } catch(e) {
                    alert_float('danger', '<?php echo _l("Export failed"); ?>');
                }
            } else {
                alert_float('danger', '<?php echo _l("Export failed"); ?>');
            }
        },
        complete: function() {
            // Restore button
            $button.prop('disabled', false).html(originalText);
        }
    });
});

// Recalculate selected employees
$('.recalculate_selected_employees').on('click', function() {
    'use strict';

    // Get all data from Handsontable
    var allData = purchase.getData();
    var sourceData = purchase.getSourceData();

    // Filter only selected rows (where checkbox is checked)
    var selectedStaffIds = [];
    for (var i = 0; i < sourceData.length; i++) {
        if (sourceData[i].select === true) {
            selectedStaffIds.push(sourceData[i].staff_id);
        }
    }

    // Check if any rows are selected
    if (selectedStaffIds.length === 0) {
        alert_float('warning', '<?php echo _l("Please select at least one employee to recalculate"); ?>');
        return;
    }

    // Get payroll ID from hidden field
    var payrollId = $('input[name="payroll_id"]').val();
    if (!payrollId) {
        alert_float('danger', '<?php echo _l("Payroll ID not found"); ?>');
        return;
    }

    // Confirm action
    if (!confirm('<?php echo _l("hr_confirm_recalculate_selected"); ?>\n\n' + selectedStaffIds.length + ' <?php echo _l("employees selected"); ?>')) {
        return;
    }

    // Show loading
    var $button = $(this);
    var originalText = $button.html();
    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l("Recalculating..."); ?>');

    // Send AJAX request to recalculate
    $.ajax({
        url: admin_url + 'hr_payroll/recalculate_payroll_employees',
        type: 'POST',
        data: {
            payroll_id: payrollId,
            staff_ids: selectedStaffIds
        },
        dataType: 'json',
        success: function(response) {
            // Debug: Log full response
            console.log('Recalculate Response:', response);
            console.log('Updated Count:', response.updated_count);
            console.log('Message:', response.message);

            if (response.success) {
                alert_float('success', response.message);

                // Reload the page to show updated data
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            } else {
                alert_float('danger', response.message);
                $button.prop('disabled', false).html(originalText);
            }
        },
        error: function(xhr) {
            console.error(xhr);
            alert_float('danger', '<?php echo _l("something_went_wrong"); ?>');
            $button.prop('disabled', false).html(originalText);
        }
    });
});

// Export payroll data to Excel
$('.export_payroll_excel').on('click', function() {
    'use strict';

    // Get all data from Handsontable
    var allData = purchase.getData();
    var sourceData = purchase.getSourceData();

    // Filter selected rows if any are selected
    var selectedStaffIds = [];
    var hasSelection = false;
    for (var i = 0; i < sourceData.length; i++) {
        if (sourceData[i].select === true) {
            selectedStaffIds.push(sourceData[i].staff_id);
            hasSelection = true;
        }
    }

    // If no selection, export all rows
    if (!hasSelection) {
        selectedStaffIds = sourceData.map(function(row) {
            return row.staff_id;
        });
    }

    // Get current month
    var month = $('#month_employees').val();

    // Get payroll_id if in payroll mode
    var payrollId = $('input[name="payroll_id"]').val() || '';

    // Prepare data
    var exportData = {
        staff_ids: selectedStaffIds,
        month: month,
        payroll_id: payrollId
    };

    // Show loading
    var $button = $(this);
    var originalText = $button.html();
    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l("Exporting..."); ?>');

    // Send AJAX request to export
    $.ajax({
        url: admin_url + 'hr_payroll/export_payroll_excel',
        type: 'POST',
        data: exportData,
        xhrFields: {
            responseType: 'blob' // Important for file download
        },
        success: function(blob, status, xhr) {
            // Get filename from Content-Disposition header or use default
            var filename = 'payroll_data_' + month + '.xlsx';
            var disposition = xhr.getResponseHeader('Content-Disposition');
            if (disposition && disposition.indexOf('filename=') !== -1) {
                var filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                var matches = filenameRegex.exec(disposition);
                if (matches != null && matches[1]) {
                    filename = matches[1].replace(/['"]/g, '');
                }
            }

            // Create download link
            var link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            alert_float('success', '<?php echo _l("Export completed successfully"); ?>');
        },
        error: function(xhr) {
            // Try to parse error message from blob
            if (xhr.responseText) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    alert_float('danger', response.message || '<?php echo _l("Export failed"); ?>');
                } catch(e) {
                    alert_float('danger', '<?php echo _l("Export failed"); ?>');
                }
            } else {
                alert_float('danger', '<?php echo _l("Export failed"); ?>');
            }
        },
        complete: function() {
            // Restore button
            $button.prop('disabled', false).html(originalText);
        }
    });
});

// Payroll Status Transition Buttons
$('.payroll-status-btn').on('click', function() {
    'use strict';

    var $btn = $(this);
    var newStatus = $btn.data('status');
    var payrollId = $btn.data('payroll-id');
    var requireConfirm = $btn.data('confirm') === true;
    var originalText = $btn.html();

    // Confirmation messages for specific status changes
    var confirmMessages = {
        'cancelled': '<?php echo _l("hr_confirm_cancel_payroll"); ?>',
        'completed': '<?php echo _l("hr_confirm_complete_payroll"); ?>',
        'submitted': '<?php echo _l("hr_confirm_submit_payroll"); ?>'
    };

    // Status display names
    var statusNames = {
        'draft': '<?php echo _l("hr_status_draft"); ?>',
        'ready_for_review': '<?php echo _l("hr_status_ready_for_review"); ?>',
        'awaiting_approval': '<?php echo _l("hr_status_awaiting_approval"); ?>',
        'submitted': '<?php echo _l("hr_status_submitted"); ?>',
        'completed': '<?php echo _l("hr_status_completed"); ?>',
        'cancelled': '<?php echo _l("hr_status_cancelled"); ?>'
    };

    // Confirm if needed
    if (requireConfirm || confirmMessages[newStatus]) {
        var message = confirmMessages[newStatus] || '<?php echo _l("Are you sure?"); ?>';
        if (!confirm(message)) {
            return false;
        }
    }

    // Save changes before status transition if in payroll mode
    if (typeof isPayrollMode !== 'undefined' && isPayrollMode) {
        // Check if there are unsaved changes
        var hasChanges = purchase.isListening(); // Check if data has been modified

        if (hasChanges) {
            var confirmSave = confirm('<?php echo _l("hr_save_changes_before_status_change"); ?>');
            if (confirmSave) {
                // Trigger save before status change
                $('.save_manage_employees').trigger('click');

                // Wait for save to complete before status change
                setTimeout(function() {
                    proceedWithStatusChange($btn, payrollId, newStatus, originalText, statusNames);
                }, 1000);
                return;
            }
        }
    }

    // Proceed with status change
    proceedWithStatusChange($btn, payrollId, newStatus, originalText, statusNames);
});

function proceedWithStatusChange($btn, payrollId, newStatus, originalText, statusNames) {
    'use strict';

    // Show loading state
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> <?php echo _l("Processing..."); ?>');

    // Send AJAX request
    $.ajax({
        url: admin_url + 'hr_payroll/change_payroll_status',
        type: 'POST',
        data: {
            payroll_id: payrollId,
            new_status: newStatus
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert_float('success', response.message);

                // Reload the page to show new status and buttons
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            } else {
                alert_float('danger', response.message);
                $btn.prop('disabled', false).html(originalText);
            }
        },
        error: function(xhr) {
            console.error(xhr);
            alert_float('danger', '<?php echo _l("something_went_wrong"); ?>');
            $btn.prop('disabled', false).html(originalText);
        }
    });
}

// ========================================
// PAY INFORMATION MODAL FUNCTIONALITY
// ========================================

// Double-click handler for pay information modal
if (typeof purchase !== 'undefined') {
    purchase.addHook('afterOnCellMouseDown', function(event, coords, TD) {
        // Only handle double-click (checking for detail property)
        if (event.detail !== 2) return;

        var colIndex = coords.col;
        var rowIndex = coords.row;
        var colData = purchase.getSettings().columns[colIndex].data;

        // Check if column is one that opens pay modal
        var payModalColumns = ['gosi_basic_salary', 'gosi_housing_allowance', 'allowance'];

        if (payModalColumns.includes(colData)) {
            var rowData = purchase.getSourceDataAtRow(rowIndex);
            var staffId = rowData.staff_id;
            var month = $('#month_employees').val();

            if (!staffId || !month) {
                alert_float('danger', 'Missing staff or month information');
                return;
            }

            openPayInfoModal(staffId, month, colData);
        }
    });

    // Right-click context menu for pay modal
    purchase.addHook('afterOnCellContextMenu', function(event, coords, TD) {
        event.preventDefault();

        var colIndex = coords.col;
        var rowIndex = coords.row;
        var colData = purchase.getSettings().columns[colIndex].data;

        var payModalColumns = ['gosi_basic_salary', 'gosi_housing_allowance', 'allowance'];

        if (payModalColumns.includes(colData)) {
            var rowData = purchase.getSourceDataAtRow(rowIndex);
            var staffId = rowData.staff_id;
            var month = $('#month_employees').val();

            if (!staffId || !month) {
                alert_float('danger', 'Missing staff or month information');
                return;
            }

            openPayInfoModal(staffId, month, colData);
        }
    });
}

// Function to open pay information modal
function openPayInfoModal(staffId, month, focusField) {
    console.log('Opening pay modal for staffId:', staffId, 'month:', month);

    $.post(admin_url + 'hr_payroll/get_pay_modal_data', {
        staff_id: staffId,
        month: month,
        [csrfData.token_name]: csrfData.hash
    }, function(resp) {
        console.log('Response received:', resp);

        if (typeof resp === 'string') {
            try {
                resp = JSON.parse(resp);
            } catch(e) {
                console.error('Failed to parse response:', e);
                alert_float('danger', 'Invalid response from server');
                return;
            }
        }

        if (resp.error) {
            alert_float('danger', resp.error);
            return;
        }

        console.log('Staff data:', resp.staff);
        console.log('Pay data:', resp.staff_pay);

        // Populate modal
        $('[name="staff_id"]').val(staffId);
        $('#pay_staff_id').val(staffId);
        $('[name="month"]').val(month);
        $('#pay_month').val(month);

        // Use 'name' field if firstname/lastname are empty
        var employeeName = resp.staff.name || ((resp.staff.firstname || '') + ' ' + (resp.staff.lastname || '')).trim();
        $('#pay_employee_name').val(employeeName);
        $('#pay_employee_number').val(resp.staff.staffid);

        console.log('Set staff_id to:', staffId, 'Field value:', $('[name="staff_id"]').val());

        // Populate fixed allowances
        if (resp.staff_pay) {
            console.log('Populating pay data...');
            console.log('Checking field IDs...');
            console.log('#pay_basic_pay exists?', $('#pay_basic_pay').length);
            console.log('#basic_pay exists?', $('#basic_pay').length);
            console.log('Basic pay value to set:', resp.staff_pay.basic_pay);

            // Try to set start_date using name or ID
            $('[name="start_date"]').val(resp.staff_pay.start_date || '');
            $('#start_date').val(resp.staff_pay.start_date || '');

            // Set payout type
            if (resp.staff_pay.payout_type === 'hourly') {
                $('[name="payout_type"][value="hourly"]').prop('checked', true);
            } else {
                $('[name="payout_type"][value="monthly"]').prop('checked', true);
            }

            // Set values using name attribute to be safe
            $('[name="basic_pay"]').val(resp.staff_pay.basic_pay || 0);
            $('[name="overtime_pay"]').val(resp.staff_pay.overtime_pay || 0);
            $('[name="food_allowance"]').val(resp.staff_pay.food_allowance || 0);
            $('[name="allowance"]').val(resp.staff_pay.allowance || 0);
            $('[name="fat_allowance"]').val(resp.staff_pay.fat_allowance || 0);
            $('[name="accomodation_allowance"]').val(resp.staff_pay.accomodation_allowance || 0);
            $('[name="mewa"]').val(resp.staff_pay.mewa || 0);

            // Populate GOSI fields from database (can be different from actual pay amounts)
            // Use explicit check for null/undefined/empty string
            var gosiBasic = (resp.staff_pay.gosi_basic !== null && resp.staff_pay.gosi_basic !== undefined && resp.staff_pay.gosi_basic !== '')
                ? parseFloat(resp.staff_pay.gosi_basic)
                : parseFloat(resp.staff_pay.basic_pay || 0);
            var gosiHousing = (resp.staff_pay.gosi_housing_allowance !== null && resp.staff_pay.gosi_housing_allowance !== undefined && resp.staff_pay.gosi_housing_allowance !== '')
                ? parseFloat(resp.staff_pay.gosi_housing_allowance)
                : parseFloat(resp.staff_pay.accomodation_allowance || 0);

            $('[name="gosi_basic"]').val(gosiBasic);
            $('[name="gosi_housing_allowance"]').val(gosiHousing);

            console.log('After setting, basic pay field value:', $('[name="basic_pay"]').val());
            console.log('GOSI Basic from DB:', resp.staff_pay.gosi_basic, 'Final value:', gosiBasic);
            console.log('GOSI Housing from DB:', resp.staff_pay.gosi_housing_allowance, 'Final value:', gosiHousing);
        } else {
            // Clear fields for new record
            $('[name="start_date"]').val('');
            $('[name="payout_type"][value="monthly"]').prop('checked', true);
            $('[name="basic_pay"], [name="overtime_pay"], [name="food_allowance"], [name="allowance"], [name="fat_allowance"], [name="accomodation_allowance"], [name="mewa"]').val(0);
            $('[name="gosi_basic"], [name="gosi_housing_allowance"]').val(0);
        }

        // Populate custom allowances
        var customHtml = '';
        if (resp.custom_allowances && resp.custom_allowances.length > 0) {
            resp.custom_allowances.forEach(function(allowance, index) {
                if (index % 2 === 0) {
                    customHtml += '<div class="row">';
                }

                var amount = resp.custom_amounts[allowance.id] || allowance.default_amount || 0;

                customHtml += '<div class="col-md-6">';
                customHtml += '<div class="form-group">';
                customHtml += '<label>' + allowance.name;
                if (allowance.name_arabic) {
                    customHtml += ' (' + allowance.name_arabic + ')';
                }
                if (allowance.is_mandatory == 1) {
                    customHtml += ' <span class="text-danger">*</span>';
                }
                customHtml += '</label>';
                customHtml += '<input type="number" step="0.01" name="custom_allowances[' + allowance.id + ']" ';
                customHtml += 'class="form-control custom-allowance-input" value="' + amount + '"';
                if (allowance.is_mandatory == 1) {
                    customHtml += ' required';
                }
                customHtml += '>';
                if (allowance.description) {
                    customHtml += '<small class="text-muted">' + allowance.description + '</small>';
                }
                customHtml += '</div>';
                customHtml += '</div>';

                if (index % 2 === 1 || index === resp.custom_allowances.length - 1) {
                    customHtml += '</div>';
                }
            });

            // Show the custom allowances section since there are allowances to display
            $('#custom-allowances-section').show();
        } else {
            customHtml = '<p class="text-muted">No custom allowances assigned to this employee.</p>';
            // Hide the custom allowances section if no allowances
            $('#custom-allowances-section').hide();
        }

        $('#custom-allowances-container').html(customHtml);

        // Show modal with focus on specific field
        $('#payInfoModal').modal('show');

        setTimeout(function() {
            if (focusField === 'gosi_basic_salary') {
                $('[name="basic_pay"]').focus();
            } else if (focusField === 'gosi_housing_allowance') {
                $('[name="accomodation_allowance"]').focus();
            } else if (focusField === 'allowance') {
                $('[name="food_allowance"]').focus();
            }
        }, 500);

        if (resp.csrf_hash) {
            csrfData.hash = resp.csrf_hash;
        }
    }).fail(function(xhr) {
        console.error('Modal data error:', xhr);
        alert_float('danger', 'Error loading pay information');
    });
}

// Save pay information
$(document).on('click', '#savePayInfo', function(e) {
    e.preventDefault();

    // Try to get staff_id from both name and ID selectors
    var staffId = $('[name="staff_id"]').val() || $('#pay_staff_id').val();
    var month = $('[name="month"]').val() || $('#pay_month').val();

    // Get payroll_id from URL if present
    var urlParams = new URLSearchParams(window.location.search);
    var payrollId = urlParams.get('payroll_id');

    console.log('Before serialize - Staff ID:', staffId, 'Month:', month, 'Payroll ID:', payrollId);

    var formData = $('#payInfoForm').serialize();

    // Explicitly add staff_id and month if not already in serialized data
    if (formData.indexOf('staff_id=') === -1 || formData.indexOf('staff_id=&') !== -1) {
        formData += '&staff_id=' + encodeURIComponent(staffId);
    }
    if (formData.indexOf('month=') === -1) {
        formData += '&month=' + encodeURIComponent(month);
    }
    if (payrollId) {
        formData += '&payroll_id=' + encodeURIComponent(payrollId);
    }

    formData += '&' + csrfData.token_name + '=' + csrfData.hash;

    console.log('Saving pay information...');
    console.log('Form data:', formData);
    console.log('Staff ID:', staffId);
    console.log('Month:', month);

    var $btn = $(this);
    var originalText = $btn.html();
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    $.post(admin_url + 'hr_payroll/update_pay_from_modal', formData, function(resp) {
        console.log('Save response:', resp);
        if (typeof resp === 'string') {
            resp = JSON.parse(resp);
        }

        if (resp.status === 'ok') {
            alert_float('success', resp.message);
            $('#payInfoModal').modal('hide');

            // Refresh the grid data without reloading the page
            console.log('Refreshing grid data...');
            employees_filterInit();

        } else {
            alert_float('danger', resp.message || 'Error saving pay information');
            $btn.prop('disabled', false).html(originalText);
        }

        if (resp.csrf_hash) {
            csrfData.hash = resp.csrf_hash;
        }
    }).fail(function(xhr) {
        console.error('Save error:', xhr);
        alert_float('danger', 'Error saving pay information');
        $btn.prop('disabled', false).html(originalText);
    });
});


</script>