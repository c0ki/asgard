var chartConfig = {
    "type": "serial",
    "theme": "light",
    "dataDateFormat": "YYYY-MM-DD",
    "legend": {
        "horizontalGap": 10,
        "maxColumns": 1,
        "position": "right",
        "useGraphSettings": true,
        "markerSize": 10,
        "autoMargins": false,
        "marginLeft": 0,
        "marginRight": 0,
        "valueWidth": 0
    },
    "valueAxes": [{
        "stackType": "regular",
        "axisAlpha": 0.3,
        "gridAlpha": 0
    }],
    "graphs": [],
    "chartScrollbar": {
        "graph": "gTotal",
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

var chart;

// Call ajax
url = "http://asgard.lxc/logtracker/dataerror";
var oReq = new XMLHttpRequest();
oReq.addEventListener("progress", chartDataProgress);
oReq.addEventListener("load", chartDataLoaded);
oReq.open('GET', url);
oReq.send();

function chartDataLoaded(event) {
    chartConfig.dataProvider = JSON.parse(this.responseText);
    chartConfig.graphs = [];
    Array.prototype.filter.call(Object.keys(chartConfig.dataProvider[0]), function (field) {
        if (field == 'date' || field.substr(0, 6) == 'query_') return;
        var graph = {
            "id": "g" + (chartConfig.graphs.length + 1),
            "fillAlphas": 0.8,
            "labelText": "[[value]]",
            "lineAlpha": 0.3,
            "color": "#000000",
            "valueField": field,
            "type": "column",
            "title": field,
            "balloonText": "[[title]]<br/>[[value]]"
        };
        if (field == 'total') {
            graph.id = "gTotal";
            graph.visibleInLegend = false;
            graph.lineThickness = 0;
            graph.type = null;
            //graph.type = false;
        }
        chartConfig.graphs.push(graph);
    });

    chart = AmCharts.makeChart("chart", chartConfig);
    chart.addListener("rendered", zoomChart);
    chart.addListener("clickGraphItem", clickGraphItem);
    zoomChart();
}

function chartDataProgress(event) {
    if (event.lengthComputable) {
        var percentComplete = event.loaded / event.total;
        var percentText = (percentComplete * 100) + '%';
        var nodeProgress = document.querySelector('#chart [data-type=progress]');
        if (nodeProgress.childNodes.length > 1) {
            nodeProgress.lastChild.innerHTML = percentText;
        }
        else {
            nodeProgress.insertAdjacentHTML('beforeend', '<span>' + percentText + '</span>');
        }
    }
}

function zoomChart() {
    chart.zoomToIndexes(chart.dataProvider.length - 40, chart.dataProvider.length - 1);
}

function clickGraphItem(obj) {
    var url = document.querySelector('#chart').dataset.url;
    var query = 'date:' + obj.item.dataContext.date;
    query += " " + obj.item.dataContext['query_' + obj.graph.title];
    window.location = url + '/' + encodeURIComponent(query);
}




