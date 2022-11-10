function verifPWD() {
    let firstPWD = document.getElementById("firstPWD");
    let mdp = firstPWD.value;
	let secondPWD = document.getElementById("secondPWD").value;
	if( mdp != secondPWD) {
		document.getElementById("button").disabled = true;
	} else {
		document.getElementById("button").disabled = false;
	}
}

function submitForm(){
	alert('apel');
	document.form.submit();
}