function getPrevMonth() {
    var prevMonth = 1;
    if (window.location.hash && window.location.hash.substring(1) == parseInt(window.location.hash.substring(1))) {
        prevMonth = window.location.hash.substring(1);
    }
    return prevMonth;
}

Array.prototype.filter.call(document.querySelectorAll('nav.zoom select'), function (node) {
    node.addEventListener("change", zoomChart);
});

function initZoom(preventMonth) {
    if (!preventMonth) {
        preventMonth = getPrevMonth();
    }
    window.location.hash = '#' + preventMonth;
    Array.prototype.filter.call(document.querySelectorAll('nav.zoom select'), function (nodeSelect) {
        Array.prototype.filter.call(nodeSelect.querySelectorAll('option'), function (nodeOption) {
            if (nodeOption.value == preventMonth) {
                nodeOption.selected = true;
            }
        });
    });
}
initZoom();

var charts = [];

var chartDefaultConfig = {
    "type": "serial",
    "theme": "light",
    "dataDateFormat": "YYYY-MM-DD",
    "legend": {
        //"horizontalGap": 10,
        //"maxColumns": 1,
        "position": "top",
        "useGraphSettings": true,
        "markerSize": 10,
        "autoMargins": false,
        "marginLeft": 0,
        "marginRight": 0,
        "valueWidth": 0,
        equalWidths: false
    },
    "valueAxes": [{
        "stackType": "regular",
        "axisAlpha": 0.3,
        "gridAlpha": 0
    }],
    "graphs": [],
    "chartScrollbar": {
        "graph": "gPreview",
        "graphType": "column",
        "oppositeAxis": false,
        "offset": 30,
        "scrollbarHeight": 50,
        "backgroundAlpha": 0,
        "selectedBackgroundAlpha": 0.1,
        "selectedBackgroundColor": "#0000ff",
        "graphFillAlpha": 0.5,
        //"graphLineAlpha": 0.5,
        "selectedGraphFillAlpha": 0.5,
        //"selectedGraphLineAlpha": 1,
        "autoGridCount": true,
        "color": "#AAAAAA"
    },
    "categoryField": "date",
    "categoryAxis": {
        boldPeriodBeginning: false,
        "parseDates": true,
        "dateFormats": [{period: 'DD', format: 'DD/MM/YYYY'},
            {period: 'WW', format: 'DD/MM/YYYY'},
            {period: 'MM', format: 'DD/MM/YYYY'},
            {period: 'YYYY', format: 'YYYY'}],
        "gridPosition": "start",
        "axisAlpha": 0,
        "gridAlpha": 0,
        "position": "left"
    },
    "export": {
        "enabled": true
    },
    "dataProvider": []
};

// Call ajax
Array.prototype.filter.call(document.querySelectorAll('.chart'), function (node) {
    var url = node.dataset.url;
    var oReq = new XMLHttpRequest();
    oReq.sourceNode = node;
    oReq.addEventListener("progress", chartDataProgress);
    oReq.addEventListener("load", chartDataLoaded);
    oReq.open('GET', url);
    oReq.send();
});

function chartDataLoaded(event) {
    var sourceNode = event.target.sourceNode;
    var data = JSON.parse(this.responseText);

    if (!data.dataset.length) {
        sourceNode.innerHTML = "No data";
        sourceNode.style.height = null;
        sourceNode.parentNode.classList.add('nodata');
        return;
    }

    var chartConfig = JSON.parse(JSON.stringify(chartDefaultConfig));
    chartConfig.dataProvider = data.dataset;
    chartConfig.dataSchema = data.schema;
    chartConfig.graphs = [];
    Array.prototype.filter.call(Object.keys(data.schema), function (field) {
        var graph = {
            "id": "g" + (chartConfig.graphs.length + 1) + sourceNode.id,
            "fillAlphas": 0.8,
            "labelText": "[[value]]",
            "lineAlpha": 0.3,
            "color": "#000000",
            "valueField": field,
            "type": "column",
            "title": field,
            "balloonText": "[[title]]<br/>[[value]]"
        };
        chartConfig.graphs.push(graph);
    });
    chartConfig.graphs.push({
        "id": "gPreview" + sourceNode.id,
        "visibleInLegend": false,
        "lineThickness": 0,
        "valueField": "preview",
        "title": "preview"
    });
    chartConfig.chartScrollbar.graph = "gPreview" + sourceNode.id;

    charts[sourceNode.id] = AmCharts.makeChart(sourceNode.id, chartConfig);
    charts[sourceNode.id].sourceNode = sourceNode;
    charts[sourceNode.id].addListener("rendered", zoomChart);
    charts[sourceNode.id].addListener("clickGraphItem", clickGraphItem);
    zoomChart({'chart': charts[sourceNode.id]});
}

function chartDataProgress(event) {
    var sourceNode = event.target.sourceNode;
    if (event.lengthComputable) {
        var percentComplete = event.loaded / event.total;
        var percentText = (percentComplete * 100) + '%';
        var nodeProgress = sourceNode.querySelector('[data-type=progress]');
        if (nodeProgress.childNodes.length > 1) {
            nodeProgress.lastChild.innerHTML = percentText;
        }
        else {
            nodeProgress.insertAdjacentHTML('beforeend', '<span>' + percentText + '</span>');
        }
    }
}

function zoomChart(obj) {
    var preventmonth = getPrevMonth();
    if (obj.target && obj.target.querySelector('option:checked') && obj.target.querySelector('option:checked').value) {
        preventmonth = obj.target.querySelector('option:checked').value;
    }
    var startDate = new Date();
    startDate.setMonth(startDate.getMonth() - preventmonth);
    //obj.chart.zoomToIndexes(obj.chart.dataProvider.length - 40, obj.chart.dataProvider.length - 1);
    if (obj.chart) {
        obj.chart.zoomToDates(startDate, new Date());
    }
    else {
        Array.prototype.filter.call(document.querySelectorAll('.chart'), function (node) {
            if (charts[node.id]) {
                charts[node.id].zoomToDates(startDate, new Date());
            }
        });
    }
    initZoom(preventmonth);
}

function clickGraphItem(obj) {
    var url = obj.chart.sourceNode.dataset.redirect;
    var query = '+date:' + obj.item.dataContext.date;
    query += " " + obj.chart.dataSchema[obj.graph.title];
    window.location = url + '/' + encodeURIComponent(query);
}




