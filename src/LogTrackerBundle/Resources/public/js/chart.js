var chartDefaultConfig = {
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
        var prevNode = sourceNode;
        while ((prevNode = prevNode.previousSibling)) {
            if (prevNode.nodeType != 3) break;
        }
        prevNode.classList.add('nodata');
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

    var chart = AmCharts.makeChart(sourceNode.id, chartConfig);
    chart.addListener("rendered", zoomChart);
    chart.addListener("clickGraphItem", clickGraphItem);
    zoomChart({'chart': chart});
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
    obj.chart.zoomToIndexes(obj.chart.dataProvider.length - 40, obj.chart.dataProvider.length - 1);
}

function clickGraphItem(obj) {
    var url = document.querySelector('#chart').dataset.redirect;
    var query = '+date:' + obj.item.dataContext.date;
    query += " " + obj.chart.dataSchema[obj.graph.title];
    window.location = url + '/' + encodeURIComponent(query);
}




