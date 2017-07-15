var selected = [];
var selectedInfo = [];
var totalPrice = 0;
var totalCoins = 0;
const STUDENT_DISCOUNT = 30;

updateTable();

$('span').click(function () {
    if ($(this).hasClass('booked')) {
        return;
    }
    var seancePrice = parseInt($('#price').attr('value'));
    var price;
    if ($(this).hasClass('low')) {
        price = seancePrice - 15;
    } else if ($(this).hasClass('high')) {
        price = seancePrice + 30;
    } else {
        price = seancePrice;
    }
    var id = $(this).attr('id');
    var data = '{"row": '+ id.split('_')[0] + ', "seat": ' + id.split('_')[1] + '}';
    var info = '{"type": "default", "coins": false, "price":'+price+'}';
    if (selected.indexOf(data) === -1) {
        selected.push(data);
        selectedInfo.push(info);
    } else {
        var index = selected.indexOf(data);
        selected.splice(index, 1);
        selectedInfo.splice(index, 1);
    }
    $('#'+$(this).attr('id')).toggleClass( "selected");
    updateTable();
});

$(document).on("change", "input:checkbox.student", function () {
    var id = $(this).attr('id');
    var info = JSON.parse(selectedInfo[id]);
    if ($(this).is(':checked')) {
        info["type"] = "student";
    } else {
        info["type"] = "default";
        info["coins"] = false;
    }
    selectedInfo[id] = JSON.stringify(info);
    updateTable();
});

$(document).on("change", "input:checkbox.coins", function () {
    var id = $(this).attr('id');
    var info = JSON.parse(selectedInfo[id]);
    info["coins"] = $(this).is(':checked');
    selectedInfo[id] = JSON.stringify(info);
    updateTable();
});


function updateTable () {
    totalPrice = 0;
    totalCoins = 0;
    var table = "";
    var price = 0;
    var studentChecked = "";
    var coinsChecked = "";
    var coins = $('#coins').attr('value');
    for (var i = 0; i < selected.length; i++) {
        info = JSON.parse(selectedInfo[i]);
        price = (info["type"] === "default") ? info["price"] : (info["price"] - STUDENT_DISCOUNT);
        if (info['coins']) {
            totalCoins += price;
            price = 0;
        }
        totalPrice += price;
    }
    for (i = 0; i < selected.length; i++) {
        var coinsDisabled = "";
        var studentDisabled = "";
        data = JSON.parse(selected[i]);
        info = JSON.parse(selectedInfo[i]);
        price = (info["type"] === "default") ? info["price"] : (info["price"] - STUDENT_DISCOUNT);
        if(totalCoins + price > coins && !info['coins']) {
            coinsDisabled = "disabled";
        }
        studentChecked = (info["type"] === "default") ? "" : "checked";
        coinsChecked = (info["coins"]) ? "checked" : "";
        if ($('#premiere').attr('value') === "true") {
            coinsDisabled = "disabled";
            studentDisabled = "disabled";
        }
        if (studentChecked === "checked") {
            coinsDisabled = "disabled";
        }
        table = table + '<tr><td>Row: '+data['row']+' Seat: '+ data['seat'] +'</td><td>'+ 'Price: '+ price+'</td></tr>';
        table = table + '<tr><td><input type="checkbox" class="student" id="'+i+'" '+studentChecked+studentDisabled+'>Student</td>' +
            '<td><input type="checkbox" class="coins" id="'+i+'" '+coinsChecked+' '+coinsDisabled+'>Pay with coins</td></tr>';
    }
    $('#totalPrice').html(totalPrice);
    $('#totalCoins').html(totalCoins);
    $('#tickets').html(table);
}

$('#booking').submit(function (e) {
    e.preventDefault();
    $.ajax({
        type: $(this).attr('method'),
        url:  $(this).attr('action'),
        data: JSON.stringify({"data": selected, "dataInfo": selectedInfo}),
        dataType    : "html",
        success: function (data, status, object) {
            window.document.write(data);
            // window.location.href = data.targetUrl;
            // $('#message').html(data.message);
        },
        error: function (data) {
            window.document.write(JSON.parse(data));
            window.document.write(data.message);
        }
    });
});