var linesnumber;
var datecompare = [];
var contentcompare = [];

function Chat(){
    this.update = ppUpdateChat;
    this.send = ppSendChat;
	this.getState = getStateOfChat;
	this.initiate = ppInitiateChat;
	this.updateCount = ppUpdateCountNumber;
}

// update lines number
function ppUpdateCountNumber(){
	jQuery.ajax({
		type: "POST",
		data:{
				'function': 'updateCount'
			},
		dataType: "json",
		success: function(data){
			linesnumber = data;
		},
	});
}

//update chat if needed
function getStateOfChat(){
	jQuery.ajax({
		type: "POST",
			   data: {  
			   			'function': 'getState'
			   },
			   dataType: "json",
			   success: function(data){
					if(data != null){
							idnumber = data;
							if(idnumber != linesnumber){
								ppUpdateChat();
							}
					}
			   },
			});
}

//send the message
function ppSendChat(message, nickname){
	jQuery.ajax({
		type: "POST",
		data:{
				'function': 'send',
				'message': message,
				'nickname': nickname
			},
		dataType: "json",
		success: function(data){
			ppUpdateChat();
		},
	});
}

//updates the chat
function ppUpdateChat(){
	     jQuery.ajax({
			   type: "POST",
			   data: {  
			   			'function': 'update'
						},
			   dataType: "json",
			   success: function(data){
				   if(data != null){
						for (var i = 0; i < data.result1.length; i++){
							if(jQuery.inArray(data.result2[i], contentcompare) > -1 && jQuery.inArray(data.result3[i], datecompare) > -1){
                            	continue;
                            }else{
                            	jQuery('#chat-area').append(jQuery("<p>" + "<span id=\"date\">" + data.result3[i] + "</span>" + "<span id=\"nick\">" + data.result1[i] + "</span>" + data.result2[i] + "</p>"));
							}
                        datecompare.push(data.result3[i]);
						contentcompare.push(data.result2[i]);
                        }
				   }
				   document.getElementById('chat-area').scrollTop = document.getElementById('chat-area').scrollHeight;
				   ppUpdateCountNumber();
			   },
			});
}

function ppInitiateChat(){
	     jQuery.ajax({
			   type: "POST",
			   data: {  
			   			'function': 'initiate'
					 },
			   dataType: "json",
			   success: function(data){
				   if(data != null){
						for (var i = 0; i < data.result1.length; i++){
                            jQuery('#chat-area').append(jQuery("<p>" + "<span id=\"date\">" + data.result3[i] + "</span>" + "<span id=\"nick\">" + data.result1[i] + "</span>" + data.result2[i] + "</p>"));
                            datecompare.push(data.result3[i]);
							contentcompare.push(data.result2[i]);
                        }
				   }
				   document.getElementById('chat-area').scrollTop = document.getElementById('chat-area').scrollHeight;
				   ppUpdateCountNumber();
			   },
			});
}