var api = "https://echo.tax:2087/admin/";
async function sendGET(path) {
    let response = await fetch(api + path);
    let data = await response.json();
    return data;
}
async function getallcounters() {
    var data = await sendGET("getallcounters");
    if (data.ok == true) {
        for (let i = 1; i < 5; i++) {
            if (data.counters[i] == false) {
                data.counters[i] = "offline";
                document.getElementById("statusBuC" + i).textContent = "Go online";
                document.getElementById("statusC" + i).innerText = "offline";
                document.getElementById("completeBuC" + i).disabled = true;
                document.getElementById("nextBuC" + i).disabled = true;
            } else {
                if (data.counters[i] != "online") {
                    document.getElementById("statusC" + i).innerText = "serveing "+data.counters[i];
                    document.getElementById("nextBuC" + i).disabled = true;
                    document.getElementById("statusBuC" + i).disabled = true;
                } else {
                    document.getElementById("statusC" + i).innerText = "online";
                    document.getElementById("completeBuC" + i).disabled = true;
                }
            }
        }
    }
}
getallcounters();
async function setstatus(i) {
    var data = await sendGET("setcounterStatus/" + i);
    if (data.ok == true) {
        if (data.online == false) {
            document.getElementById("statusBuC" + i).textContent = "Go online";
            document.getElementById("statusC" + i).innerText = "offline";
            document.getElementById("completeBuC" + i).disabled = true;
            document.getElementById("nextBuC" + i).disabled = true;
        } else {
            document.getElementById("statusBuC" + i).textContent = "Go offline";
            document.getElementById("statusC" + i).innerText = "online";
            document.getElementById("completeBuC" + i).disabled = true;
            document.getElementById("nextBuC" + i).disabled = false;
        }
    } else {
        alert(data.msg)
    }
}
async function completeTicet(i) {
    var data = await sendGET("setcounterComplete/" + i);
    if (data.ok == true) {
        document.getElementById("statusBuC" + i).textContent = "Go offline";
        document.getElementById("statusC" + i).innerText = "online";
        document.getElementById("completeBuC" + i).disabled = true;
        document.getElementById("nextBuC" + i).disabled = false;
    } else {
        alert(data.msg)
    }
}
async function nextTicet(i) {
    var data = await sendGET("setcounterNext/" + i);
    if (data.ok == true) {
        document.getElementById("statusC" + i).innerText = "serveing "+data.ticket;
        document.getElementById("completeBuC" + i).disabled = false;
        document.getElementById("nextBuC" + i).disabled = true;
    } else {
        alert(data.msg)
    }
}
