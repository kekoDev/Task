var api = "https://echo.tax:2087/";
function setCookie(name, value, days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}
function eraseCookie(name) {
    document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}
async function sendGET(path) {
    let response = await fetch(api + path);
    let data = await response.json();
    return data;
}
async function TakeNumber() {
    var data = await sendGET("newticket");
    if (data.ok == true) {
        document.getElementById("TakeNumberBu").textContent = "Your Number : " + data.id;
        document.getElementById("TakeNumberBu").disabled = true;
        setCookie("MyNumber", data.id, 1);
        document.getElementById("lastNumber").innerText = data.id;
    } else {
        alert(data.msg);
    }
}
async function GetUpdate() {
    var data = await sendGET("GetUpdate");
    if (data.ok == true) {
        if (data.newserving == getCookie("MyNumber")) {
            document.getElementById("nowServing").innerText = data.newserving + " (You)";
        } else {
            if (data.newserving == false){
                document.getElementById("nowServing").innerText = "no one";
            }else{
                document.getElementById("nowServing").innerText = data.newserving;
            }
        }
        document.getElementById("lastNumber").innerText = data.lastnumber;
        document.getElementById("myinfo").innerText = "";
        for (let i = 1; i < 5; i++) {
            if (data.counters[i] == false) {
                data.counters[i] = "offline";
                document.getElementById("statusC" + i).innerText = "offline";
            } else {
                if (data.counters[i] != "online") {
                    document.getElementById("statusC" + i).innerText = "serveing " + data.counters[i];
                    if (data.counters[i] == getCookie("MyNumber")) {
                        document.getElementById("myinfo").innerText = "You are serveing in counter "+i;
                    }
                } else {
                    document.getElementById("statusC" + i).innerText = "online";
                }
            }
        }
        if (data.lastnumber > getCookie("MyNumber") && document.getElementById("myinfo").value != ""){
            document.getElementById("myinfo").innerText = "";
            eraseCookie("MyNumber");
            document.getElementById("TakeNumberBu").disabled = false;
            document.getElementById("TakeNumberBu").textContent = "Take a Number";
        }
    } else {
        alert(data.msg);
    }
    setTimeout(GetUpdate, 5000);
}
GetUpdate();
//////////
if (getCookie("MyNumber")) {
    document.getElementById("TakeNumberBu").textContent = "Your Number : " + getCookie("MyNumber");
    document.getElementById("TakeNumberBu").disabled = true;
}
