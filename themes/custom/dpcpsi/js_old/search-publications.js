function clearText(obj) {
	if (obj.value == "Publications Search") {
		obj.value = "";
		obj.style.color = "black";
	}
}

function searchPub(f) {

   // var formValidated = true;
	var strText = document.getElementById("strSearch").value;
	
		if (strText == "Publications Search")
		{
			strText = "";
		}
		else
		{			
		document.location.href = "publications?strSearch=" + strText;
		}
		return true;
	
}