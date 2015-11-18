/**
 * Load google chart
 */
google.load('visualization', '1', {packages: ['corechart']});

/**
 * jQuery on-load functions
 */
jQuery(function($) {
  "use strict";

  var $defaultCurrency = $('#mdl'),
      $allCurrencies = $('.currency');

  function refreshRates($activeInput) {
    var inputValue = $activeInput.val(),
        inputRate = $activeInput.data('rate'),
        inputId = $activeInput.attr('id'),
        coef = 0;

    if (!isNaN(inputValue)) {
      coef = inputValue * inputRate;
    }

    $allCurrencies.each(function(i, item) {
      var $item = $(item),
          itemRate = $item.data('rate');

      if ($item.attr('id') != inputId) {
        $item.val(calculateRate(coef/itemRate));
      }
    });
  }

  function calculateRate(value) {
    value = ('Infinity' != typeof value) ? value : 0;
    return parseFloat(value).toFixed(3)
  }

  $allCurrencies.on('keyup', function() {
    refreshRates($(this));
  });

  refreshRates($defaultCurrency);

  function prepareData(ratesData) {
    var ratesArray = ratesData || chartValues,
      rates = JSON.parse(ratesArray),
      data = [],
      max = rates[0].rate,
      min = max;

    rates.forEach(function(row) {
      var date = new Date(row.created),
        rate = parseFloat(row.rate);

      date.setHours(null);
      date.setMinutes(null);
      date.setSeconds(null);

      min = min > rate ? rate : min;
      max = max < rate ? rate : max;
      data.push([date, rate]);
    });

    drawChart(data, min, max);
  }

  function drawChart(data, min, max) {
    var table = new google.visualization.DataTable();

    table.addColumn('date', 'Time of Day');
    table.addColumn('number', '');
    table.addRows(data);

    var options = {
      title: null,
      legend: { position: "none" },
      hAxis: {
        format: 'd.M.yy'
      },
      vAxis: {
        gridlines: {color: 'none'},
        minValue: min,
        maxValue: max
      }
    };

    var chart = new google.visualization.LineChart(document.getElementById('mge-chart'));
    chart.draw(table, options);
  }

  $('.currency-code').on('click', function() {
    var code = $(this).data('code');
    $.get(
      '/wp-admin/admin-ajax.php',
      {'action':'chart_data', 'code': code },
      function(response) {
        prepareData(response);
      }
    );
  });

  google.setOnLoadCallback(prepareData());

});
