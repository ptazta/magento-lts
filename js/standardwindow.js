function open_Redsys_window(){
	var iWinState = 1;
	var objForm = null;

	if(document.Redsys)
		objForm = document.Redsys;
	else
		objForm = document.getElementById("Redsys");

	if(objForm.windowstate)
	iWinState = objForm.windowstate.value;

	if (iWinState == "1") {
		//popup window
		var serviwin = window.open("","Redsys_window","height=600,width=525,menubar=0,resizable=1,scrollbars=1,status=1,titlebar=0,toolbar=0,left=100,top=50");

		if (serviwin)
			serviwin.focus();

		objForm.target = "Redsys_window";
	} else
		objForm.target = "";

	objForm.submit();
}