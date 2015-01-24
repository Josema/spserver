import mx.controls.gridclasses.DataGridColumn;
import mx.styles.CSSStyleDeclaration;
import mx.transitions.*;
import mx.transitions.easing.*;

//chat.as
var ignore_list:Array = new Array();
var nc:NetConnection = new NetConnection();
var privateMsg:Array = new Array();

// remote function
nc.joinuser=NewUserjoin;
nc.removeuser=removeUser;
nc.receivePublicMsg=receivePublicMsg;
nc.receivePrivateMsg=receivePrivateMsg;
nc.handRequested=handRequested;
nc.talkStarted=talkStarted;
nc.talkEnded=talkEnded;
nc.getNumberUsersConnected=getNumberUsersConnected;
nc.IhaveBeenKicked=IhaveBeenKicked;
nc.IhaveBeenBanned=IhaveBeenBanned;
nc.changeProfil=changeProfil;
nc.receiveRequestPrivate=receiveRequestPrivate;
nc.invitationAccepted=invitationAccepted;
nc.privateInvitationStopped=privateInvitationStopped;



_root.Application.people_grd.addEventListener("change",selectUserInPeopleGrid);





_root.Application.sendprive_pb.onPress=sendPrivateMessage;
_root.Application.send_pb.onPress=sendPublicMessage;
_root.Application.msgPublic_txt.addEventListener("enter",sendPublicMessage);
_root.Application.msgPrivate_txt.addEventListener("enter",sendPrivateMessage);
_root.login.login_txt.addEventListener("enter",loginKeyDown);
_root.login.password_txt.addEventListener("enter",loginKeyDown);

_root.Application.closeVideo_btn.onPress=closeVideo;
_root.Application.talk_btn.onPress=talking;
_root.Application.talk_btn.onRelease=_root.Application.talk_btn.onReleaseOutside=notTalking;

_root.Application.askHand_mc.onPress=askHand;
_root.Application.colorPublic_btn.onPress=showPublicColorPalette;
_root.Application.colorPrivate_btn.onPress=showPrivateColorPalette;
_root.Application.smileyPublic_btn.onPress=showSmileyPublicPanel;
_root.Application.smileyPrivate_btn.onPress=showSmileyPrivatePanel;
_root.Application.webcam1_btn.onPress=webcam1Click;
_root.Application.webcam4_btn.onPress=webcam4Click;
_root.Application.clearPrivate_pb.onPress = clearPrivate;
_root.Application.clearPublic_pb.onPress = clearPublic;
_root.Application.info_btn.onPress = infoUser;
_root.Application.ignore_btn.onPress = ignoreUser;

_root.Application.av4.max1_btn.onPress=maximizeWebcam1;
_root.Application.av4.max2_btn.onPress=maximizeWebcam2;
_root.Application.av4.max3_btn.onPress=maximizeWebcam3;
_root.Application.av4.max4_btn.onPress=maximizeWebcam4;
// rollovers : this is compilcated because of delegating events 
_root.Application.av2.onRollOver=rolloverWebcam;
_root.Application.av2.onRollOut=rolloutWebcam;
_root.Application.av4.clip1.onRollOver=rolloverWebcam1;
_root.Application.av4.clip1.onRollOut=rolloutWebcam1;
_root.Application.av4.clip2.onRollOver=rolloverWebcam2;
_root.Application.av4.clip2.onRollOut=rolloutWebcam2;
_root.Application.av4.clip3.onRollOver=rolloverWebcam3;
_root.Application.av4.clip3.onRollOut=rolloutWebcam3;
_root.Application.av4.clip4.onRollOver=rolloverWebcam4;
_root.Application.av4.clip4.onRollOut=rolloutWebcam4;

_root.Application.av4.close1_btn.onPress=closeWebcam1;
_root.Application.av4.close2_btn.onPress=closeWebcam2;
_root.Application.av4.close3_btn.onPress=closeWebcam3;
_root.Application.av4.close4_btn.onPress=closeWebcam4;

_root.Application.endPrivate_btn.onPress=_root.Application.privateCacheHeader_mc.stopPrivateLnk_btn.onPress=stopPrivateInvitation;

// invitations functions
_root.Application.private_btn.onPress=requestPrivateChat;


// drag movies functions
_root.Application.window_mc.header_mc.onPress = moveWindow;
_root.Application.window_mc.header_mc.onRelease = stopMoveWindow;

_root.rooms_mc.roomLove_btn.onPress=enterLoveRoom;
_root.rooms_mc.roomFriend_btn.onPress=enterFriendRoom;
_root.rooms_mc.roomHot_btn.onPress=enterHotRoom;
_root.rooms_mc.termsOfUse_btn.onPress=termsOfUse;

_root.Application.window_talk.header_mc.onPress = moveWindow;
_root.Application.window_talk.header_mc.onRelease = stopMoveWindow;	
_root.Application.window_talk.closeWindow_btn.onPress=closeTalk;
		

function termsOfUse() {
	getURL("termsOfUse.php","_new");
}

function enterFriendRoom() {
	_root.room="chat";
	_root.rtmpString = "rtmp://"+_root.IP+':'+_root.port+"/"+_root.room;
	init_chat();
	TransitionManager.start(rooms_mc, {type:Squeeze, direction:1, duration:2, easing:Elastic.easeIn, dimension:1});
}
function enterLoveRoom() {
	_root.room="chat";
	_root.rtmpString = "rtmp://"+_root.IP+':'+_root.port+"/"+_root.room;
	init_chat();
	TransitionManager.start(rooms_mc, {type:Squeeze, direction:1, duration:2, easing:Elastic.easeIn, dimension:1});
}
function enterHotRoom() {
	_root.room="chat";
	_root.rtmpString = "rtmp://"+_root.IP+':'+_root.port+"/"+_root.room;
	init_chat();
	TransitionManager.start(rooms_mc, {type:Squeeze, direction:1, duration:2, easing:Elastic.easeIn, dimension:1});
}




_root.Application.av1.closeMyWebcam_btn.onPress=function() {
	this._parent._visible=false;
}
function closeWebcam1() {
	_root.in_stream1.close();
	_root.Application.av4.max1_btn._visible=false
	_root.Application.av4.lock1._visible=false;
	_root.Application.av4.close1_btn._visible=false;
	_root.Application.av4.video1._visible=false;
	_root.Application.av4.pseudo1_txt.text="";
	_root.Application.av4.video1.busy=false;
	_root.Application.av4.lock1.lockedVideo=false;
	_root.nextCam=0;
}
function closeWebcam2() {
	_root.in_stream2.close();
	_root.Application.av4.max2_btn._visible=false
	_root.Application.av4.lock2._visible=false;
	_root.Application.av4.close2_btn._visible=false;
	_root.Application.av4.video2._visible=false;
	_root.Application.av4.pseudo2_txt.text="";
	_root.Application.av4.video2.busy=false;
	_root.Application.av4.lock2.lockedVideo=false;
	_root.nextCam=1;
}
function closeWebcam3() {
	_root.in_stream3.close();
	_root.Application.av4.ma3_btn._visible=false
	_root.Application.av4.lock3._visible=false;
	_root.Application.av4.close3_btn._visible=false;
	_root.Application.av4.video3._visible=false;
	_root.Application.av4.pseudo3_txt.text="";
	_root.Application.av4.video3.busy=false;
	_root.Application.av4.lock3.lockedVideo=false;
	_root.nextCam=2;
}
function closeWebcam4() {
	_root.in_stream4.close();
	_root.Application.av4.max4_btn._visible=false
	_root.Application.av4.lock4._visible=false;
	_root.Application.av4.close4_btn._visible=false;
	_root.Application.av4.video4._visible=false;
	_root.Application.av4.pseudo4_txt.text="";
	_root.Application.av4.video4.busy=false;
	_root.Application.av4.lock4.lockedVideo=false;
	_root.nextCam=3;
}


function loginKeyDown() {
	if (Key.isDown(Key.ENTER)) {
		_root.pseudo=_root.login.login_txt.text;
		_root.password=_root.login.password_txt.text;
		login_chat();
	}	
}
function rolloverWebcam1() {
	_root.Application.av4.max1_btn._visible=true;
	_root.Application.av4.lock1._visible=true;
	_root.Application.av4.close1_btn._visible=true;
}
function rolloutWebcam1() {
	// -64 !
	if ((this._ymouse>-45 || this._ymouse<-63) && (_root.Application.av4.lock1.lockedVideo==false)) {
		_root.Application.av4.max1_btn._visible=false;
		_root.Application.av4.lock1._visible=false;
		_root.Application.av4.close1_btn._visible=false;
	}
}
function rolloverWebcam2() {
	_root.Application.av4.max2_btn._visible=true;
	_root.Application.av4.lock2._visible=true;
	_root.Application.av4.close2_btn._visible=true;
}
function rolloutWebcam2() {
	if ((this._ymouse>-45 || this._ymouse<-63) && (_root.Application.av4.lock2.lockedVideo==false)) {
		_root.Application.av4.max2_btn._visible=false;
		_root.Application.av4.lock2._visible=false;
		_root.Application.av4.close2_btn._visible=false;
	}
}
function rolloverWebcam3() {
	_root.Application.av4.max3_btn._visible=true;
	_root.Application.av4.lock3._visible=true;
	_root.Application.av4.close3_btn._visible=true;
}
function rolloutWebcam3() {
	if ((this._ymouse>-45 || this._ymouse<-63) && (_root.Application.av4.lock3.lockedVideo==false)) {
		_root.Application.av4.max3_btn._visible=false;
		_root.Application.av4.lock3._visible=false;
		_root.Application.av4.close3_btn._visible=false;
	}
}
function rolloverWebcam4() {
	_root.Application.av4.max4_btn._visible=true;
	_root.Application.av4.lock4._visible=true;
	_root.Application.av4.close4_btn._visible=true;
}
function rolloutWebcam4() {
	if ((this._ymouse>-45 || this._ymouse<-63) && (_root.Application.av4.lock4.lockedVideo==false)) {
		_root.Application.av4.max4_btn._visible=false;
		_root.Application.av4.lock4._visible=false;
		_root.Application.av4.close4_btn._visible=false;
	}
}



function startPrivateGUI() {
	delete(_root.Application.av2.onRollOver);
	TransitionManager.start(_root.Application.privateCacheHeader_mc, {type:Zoom, direction:0, duration:1, easing:Bounce.easeOut});
	//TransitionManager.start(_root.Application.privateChatMask_mc, {type:Fly, direction:0, duration:3, easing:Elastic.easeOut, startPoint:9}); 

	_root.Application.privateChatMask_mc._visible=true;
	_root.Application.av1._visible=true;
	_root.privateAudio_sound.setVolume(100);
	_root.Application.private_btn.enabled=false;
	_root.Application.info_btn.enabled=false;
	_root.Application.ignore_btn.enabled=false;
	_root.Application.askHand_mc.visible=false;
	_root.Application.talk_btn.visible=false;
	_root.Application.webcam1_btn.enabled=false;
	_root.Application.endPrivate_btn._visible=true;
	_root.Application.closeVideo_btn._visible=false;
	webcam4Click();

}
function endPrivateGUI() {
	TransitionManager.start(_root.Application.privateCacheHeader_mc, {type:Zoom, direction:1, duration:1, easing:Bounce.easeOut});
	_root.Application.av2.onRollOver=rolloverWebcam;
	_root.Application.closeVideo_btn._visible=true;	
	_root.Application.lockUnlock_btn._visible=true;	
	_root.Application.privateChatMask_mc._visible=false;
	_root.privateAudio_sound.setVolume(0);
	_root.Application.people_grd.enabled=true;
	_root.Application.private_btn.enabled=true;
	_root.Application.info_btn.enabled=true;
	_root.Application.ignore_btn.enabled=true;
	_root.Application.askHand_mc.visible=true;
	_root.Application.talk_btn.visible=true;
	_root.Application.webcam1_btn.enabled=true;
	_root.Application.endPrivate_btn._visible=false;
	_root.in_stream.close();
		
}


function showHint(msg:String,x,y:Number) {
		_root.Application.help_mc.txt_txt.text=msg;
		_root.Application.help_mc._x=x;
		_root.Application.help_mc._y=y;
		TransitionManager.start(_root.Application.help_mc, {type:Zoom, direction:0, duration:1, easing:Bounce.easeOut, param1:empty, param2:empty});
}
function receiveRequestPrivate(fromWho,fromWhoID) {
		
		if (_root.Application.people_grd.enabled==false) return;
		if (_root.Application.acceptCheckbox.checked==false) return;
		if (isUserInIgnoreList(fromWho) == true) return;
		if (num==undefined) num=0;
		num++;		
		offset=(num % 10);
		duplicated = _root.Application.window_invite.duplicateMovieClip(fromWho+num,99+num, {_x:400+offset*20,_y:200+offset*20});
		duplicated .pseudo_txt.text=fromWho;
		duplicated.fromWho=fromWho;
		duplicated.fromWhoID=fromWhoID;
		trace("duplicated num="+num+"fromWho="+fromWho+" fromWhoID="+fromWhoID);
		duplicated.description_txt.text=fromWho+" would like to invite you for a private Chat. Do you want to accept ?";
		duplicated.header_mc.onPress = moveWindow;
		duplicated.header_mc.onRelease = stopMoveWindow;	
		duplicated.accept_btn.onPress=acceptInvitation;
		duplicated.closeWindow_btn.onPress=duplicated.deny_btn.onPress=function() {
			removeMovieClip(this._parent);
		}

		TransitionManager.start(duplicated , {type:Zoom, direction:0, duration:1, easing:Bounce.easeOut, param1:empty, param2:empty});
	
}
function stopPrivateInvitation() {
	var myID=_root.user.id;
	nc.call("closePrivateChat",null,myID,_root.privateChatterID);
	trace("stopPrivateInvitation myID "+myID+" privateChatterID:"+_root.privateChatterID);
	_root.user.onlineStatus=true;
	//public void stopPrivateInvitation(String myID,String privateChatterID)
	nc.call("stopPrivateInvitation",null,myID,_root.privateChatterID);
	changeMyProfil();
	endPrivateGUI();
}

function privateInvitationStopped(stoppedByID:String) {
	trace("*privateInvitationStopped*");
	_root.user.onlineStatus=true;
	changeMyProfil();
	endPrivateGUI();
}

function acceptInvitation() {
		postedBy=this._parent.fromWho;
		postedByID=this._parent.fromWhoID;
		selectUserinDataGrid(postedByID);
		_root.privateChatterID=postedByID;
		//String whoAccepts,String whoAcceptsId,String whoHasInvited,String whoHasInvitedID)
		nc.call("acceptInvitation",null,_root.user.pseudo,_root.user.id,postedBy,postedByID);
		removeMovieClip(this._parent);
		_root.user.onlineStatus=false;
		startPrivateGUI();
		changeMyProfil();
		_root.playUserVideo(postedBy);

				
}


function invitationAccepted(whoAccepts,whoAcceptsId:String) {
	trace("Invitation was accepted by"+whoAccepts+" id="+whoAcceptsId);
	selectUserinDataGrid(whoAcceptsId);
	_root.privateChatterID=whoAcceptsId;
	_root.user.onlineStatus=false;
	startPrivateGUI();
	changeMyProfil();
	_root.playUserVideo(whoAccepts);
	// et également sa voix !
}
//
function selectUserinDataGrid(id:String) {
	for (i=0; i<_root.Application.people_grd.length; i++) {
		item=_root.Application.people_grd.getItemAt(i);
		if (item.id == id) {
			_root.Application.people_grd.selectedIndex=i;
			return;
		}
	}
}



function requestPrivateChat() {
	trace("*");
		item=_root.Application.people_grd.selectedItem;
		whoId=item.id;
		trace("requestPrivateChat from:"+_root.user.pseudo+"with:"+whoId);
		if (item==undefined) {
			showHint("Select an user from the left to send an invitation !",826,-126);
			return;		
		}
	//requestPrivate(String whoRequests,String whoRequestsID,String whoIsIvitedID)
	trace("_root.user.id="+_root.user.id);
	nc.call("requestPrivate",null,_root.user.pseudo,_root.user.id,whoId);	
	// timer to avoid calling user not more than 10 seconds !
	timerrequestPrivateChat=setInterval(reEnableRequestPrivateChat,5000);
	_root.Application.private_btn.enabled=false;
}
function reEnableRequestPrivateChat() {
	trace("reEnableRequestPrivateChat");
	_root.Application.private_btn.enabled=true;
	clearInterval(timerrequestPrivateChat);
}

function privateChatRequested(byWho:String) {
}




function rolloverWebcam() {
		_root.Application.lockUnlock_btn._visible=true;
		_root.Application.closeVideo_btn._visible=true;
}

function rolloutWebcam() {
	if ((this._ymouse>20) && (_root.Application.lockUnlock_btn.lockedVideo==false)) {
		_root.Application.lockUnlock_btn._visible=false;
		_root.Application.closeVideo_btn._visible=false;
	}
}

function moveWindow() {
	startDrag(this._parent);
	this._parent.swapDepths(99);
}
function stopMoveWindow() {
	this.stopDrag();
}



function infoUser() {
	if (_root.authentificate==false) return;
	who=_root.Application.people_grd.selectedItem.username;
	if (who==undefined) return;
	_root.Application.window_mc._visible=true;
	_root.Application.window_mc.pseudo_txt.text=who;
	_root.Application.window_mc.photo_mc.loadMovie(who+".jpg");
	// look for other data !
		var myVars = new LoadVars();
		myVars.pseudo = who;
		myVars.sendAndLoad(_root.infoUserURL, myVars, "");
		myVars.onLoad = function(success) {
			trace("myVars.description="+myVars.description+"myVars.age="+myVars.age);
			_root.Application.window_mc.country_txt.text=myVars.country;
			_root.Application.window_mc.age_txt.text=myVars.age;
			_root.Application.window_mc.description_txt.text=myVars.description;
			
		}
	
}


function clearPrivate() {
	_root.Application.historyPrivate.clearText();
	who=_root.Application.people_grd.selectedItem.username;
	trace("who="+who);
	privateMsg[who]="";
}	
function clearPublic() {
	_root.Application.historyPublic.clearText();
}	


function webcam1Click() {
//	in_stream.close();
	_root.Application.webcam_txt.text="4 Webcams";
	_root.Application.webcam1_btn._visible=false;
	_root.Application.webcam4_btn._visible=true;
	_root.webcams4=true;
	_root.Application.av4._visible=true;
	_root.Application.av2._visible=false;
	

}
function webcam4Click() {
//	in_stream.close();
	_root.Application.webcam_txt.text="Webcam";
	_root.Application.webcam1_btn._visible=true;
	_root.Application.webcam4_btn._visible=false;	
	_root.webcams4=false;	
	_root.Application.av4._visible=false;
	_root.Application.av2._visible=true;
	_root.Application.av2.outgoing._visible
}


peopleIconFunction = function (itemObj:Object, columnName:String) {
	if (itemObj == undefined || columnName == undefined) {
		return;
	}
	switch (columnName) {
	case "sex" :
		var sex = itemObj.sex;
		var onlineStatus=itemObj.onlineStatus;
		icone="icon_n"+sex+onlineStatus;
		return icone;
		break;
	case "webcam" :
		var webcam = itemObj.webcam;
		if (webcam == "true") return "webcamIcon";  else 	return;
		break;
	}
};
//public void changeProfil(String webcam,String role, String status, String isWatching)
function changeMyProfil() {
	trace("changeMyProfil cam:"+_root.user.webcam+" status="+_root.user.onlineStatus);
	nc.call("changeProfil",null,_root.user.webcam,_root.user.role,_root.user.onlineStatus);
}
function changeProfil(id,webcam,role, onlineStatus) {
	trace("changeProfil*"+id+" :"+webcam+" onlineStatus:"+onlineStatus);
	// search the user !
	for (i=0; i<_root.Application.people_grd.length; i++) {
		item=_root.Application.people_grd.getItemAt(i);
		if (item.id == id) {
			// found
			item.webcam=webcam;
			item.role=role;
			item.onlineStatus=onlineStatus;
			_root.Application.people_grd.replaceItemAt(i,item);
			return;
		}
	}
}



function initPeopleGrid() {

	_root.Application.people_grd.removeAllColumns();
	_root.Application.people_grd.removeAll();
	_root.Application.people_grd.columnNames = ["webcam", "sex", "username"];
	//customize columns
	var col:mx.controls.gridclasses.DataGridColumn;
	col = _root.Application.people_grd.getColumnAt(0);
	col.width = 30;
	col.headerText = "Cam";
	col.cellRenderer = "IconCellRenderer";
	col["iconFunction"] = peopleIconFunction;
	//
	col = _root.Application.people_grd.getColumnAt(1);
	col.width = 30;
	col.headerText = "Sex";
	col.cellRenderer = "IconCellRenderer";
	col["iconFunction"] = peopleIconFunction;
	//
	col = _root.Application.people_grd.getColumnAt(2);
	col.width = 250;
	col.headerText = "Username";
	// --------------------
	// Style for DataGrid
	//
	var styleObj:CSSStyleDeclaration = new mx.styles.CSSStyleDeclaration();
	styleObj.styleName = "myStyle";
	styleObj.fontFamily = "Verdana";
	styleObj.fontWeight = "normal";
	styleObj.fontSize = 10;
	
	//bold
	styleObj.color = 0x334455;
	_global.styles["myStyle"] = styleObj;
	_root.Application.people_grd.setStyle("styleName", "myStyle");
	_root.Application.people_grd.setStyle("alternatingRowColors", Array(0xFFFFFF, 0xECF2F8));
	_root.Application.people_grd.setRowHeight(24);
	_root.Application.people_grd.setStyle("hGridLines", false);
	_root.Application.people_grd.setStyle("hGridLineColor", 0xA2A6A9);
	_root.Application.people_grd.setStyle("vGridLines", false);
	_root.Application.people_grd.resizableColumns = false;
	//people_grd.setVScrollPolicy("off");
	// themeColor
	//people_grd.setStyle("themeColor", "haloBlue"); 
	// Header
	// No header in this example
	_root.Application.people_grd.setShowHeaders(true);
}




function displayVolume() {
	_root.Application.av2.levelVolume_mc.maskVolume_mc._height=mic.activityLevel*2;
}
function closeVideo() {
	_root.in_stream.close();
	_root.Application.av2.outgoing._visible=false;
	_root.Application.closeVideo_btn._visible=false;
	_root.Application.lockUnlock_btn._visible=false;
	//_root.Application.av4._visible=false;	
}

function publishMyWebcam() {
		//mic
		volumeID = setInterval(displayVolume, 50);
		mic = Microphone.get();
		mic.setRate(11);
		mic.useEchoSuppression=true;
		// and cam!
		cam = Camera.get();
		cam.setMode(160, 120, 5);
		cam.setQuality(0, 80);
		if (cam.muted==true) {
			if (_root.userMustPublishWebcam==true) System.showSettings(0);
		}	
		if (Camera.names.length==0) {
			trace("no webcam found!");
		} else {
			//nc.call("changeStatus",null,myuser.pseudo,2);
			trace("webcam found");
		}
		cam.onStatus = function(infoObj:Object) {
			switch (infoObj.code) {
			case 'Camera.Muted' :
				trace("Camera access is denied");
				System.showSettings(0);
				_root.user.webcam=false;
				changeMyProfil();
				break;
			case 'Camera.Unmuted' :
				trace("Camera access granted");
				_root.user.webcam=true;
				changeMyProfil();
				break;
			}
		}

		_root.Application.av1.outgoing.attachVideo(cam);		
		out_stream = new NetStream(nc);		
		out_stream.attachVideo(cam);
		out_stream.attachAudio(mic);		
		out_stream.publish(_root.user.pseudo, "live");	
		
}
function talking() {
	if (_root.listenToTalk==false) return;
	if (_root.user.onlineStatus==false) return;
	trace("I am talking");
	nc.call("startTalk",null,_root.user.pseudo);

}
function closeTalk() {
	_root.in_streamTalk.close();
	_root.Application.window_talk._visible=false;
}
function notTalking() {
	nc.call("stopTalk",null,_root.user.pseudo);

}

function talkEnded(who) {
	if (_root.user.pseudo==who) return;
	if (_root.user.onlineStatus==false) return;
	TransitionManager.start(_root.Application.window_talk, {type:Zoom, direction:1, duration:1, easing:Bounce.easeOut});

	_root.Application.talk_btn._visible=true;
	_root.Application.talk_txt.text="";
	_root.whoIsSpeaking=undefined;
	_root.in_streamTalk.close();
	/*streamListen.close();
	_root.Application.av2.outgoing.attachAudio(false);*/
	
}

function talkStarted(who) {
	if (_root.listenToTalk==false) return;
	if (_root.user.onlineStatus==false) return;
	trace("talkStarted by"+who);
	if (_root.user.pseudo!=who) _root.Application.talk_btn._visible=false;
	_root.Application.window_talk.pseudo_txt.text=who;
	_root.Application.talk_txt.text=who+" is speaking";
	// tes if I am speaking !
	_root.whoIsSpeaking=who;
	if (_root.user.pseudo==who) return;
	TransitionManager.start(_root.Application.window_talk, {type:Zoom, direction:0, duration:1, easing:Bounce.easeOut});

	_root.in_streamTalk = new NetStream(nc);
	_root.Application.window_talk.outgoing.attachVideo(_root.in_streamTalk);
	_root.Application.window_talk.attachAudio(_root.in_streamTalk);
	_root.talkAudio_sound = new Sound(_root.Application.av2);
	_root.talkAudio_sound.setVolume(100);
	_root.in_streamTalk.play(who);
}
function getNumberUsersConnected(numbers) {
	_root.login.numberConnectedUsers_txt.text="Connected users:"+numbers;
}



/*
kick  an user whose userName is who
**/

function kick(who:String) {
	trace("kick:"+who);
	nc.call("kick",null,who);
}
/*
ban an user whose userName is who
**/
function ban(who:String) {
	trace("ban:"+who);
	nc.call("ban",null,who);
	
}
/* called when an admin kicks anuser
**/
function IhaveBeenKicked(id:String) {
	trace("I have been kicked");
	disable_GUI();	
	_root.login._visible=false;
	_root.attachMovie("window_kicked", "kickwindow", this.getNextHighestDepth(), {_x:500, _y:400});
	kickwindow.header_mc.onPress = moveWindow;
	kickwindow.header_mc.onRelease = stopMoveWindow;	
	kickwindow.closeWindow_btn.onPress=duplicated.deny_btn.onPress=function() {
		removeMovieClip(this._parent);
	}
	_root.Application.people_grd._visible=false;
	TransitionManager.start(kickwindow , {type:Zoom, direction:0, duration:1, easing:Bounce.easeOut, param1:empty, param2:empty});
	nc.close;	
}
function IhaveBeenBanned(id:String) {
	trace("I have been kicked");
	disable_GUI();	
	_root.login._visible=false;
	_root.attachMovie("window_kicked", "kickwindow", this.getNextHighestDepth(), {_x:500, _y:400});
	kickwindow.header_mc.onPress = moveWindow;
	kickwindow.header_mc.onRelease = stopMoveWindow;	
	kickwindow.closeWindow_btn.onPress=duplicated.deny_btn.onPress=function() {
		removeMovieClip(this._parent);
	}
	_root.Application.people_grd._visible=false;
	TransitionManager.start(kickwindow , {type:Zoom, direction:0, duration:1, easing:Bounce.easeOut, param1:empty, param2:empty});
	nc.close;		
}





		
function handRequested(whoRequested) {
	_root.Application.handAsked_mc._visible=true;
	_root.Application.handAsked_mc.handRequested_txt.text=whoRequested+" wants talk";
	intervalId = setInterval(executeCallback, 1000);
	playSound("handAsked");
	function executeCallback():Void {
		_root.Application.handAsked_mc._visible=false;
		_root.Application.handAsked_mc.handRequested_txt.text="";
		clearInterval(intervalId);
}
 
}
function playSound(snd) {
	if (_root.playSounds==false) return;
	var my_sound:Sound = new Sound();
	my_sound.attachSound(snd);
	my_sound.start();
}

function askHand() {
	trace("askHand");
	//_root.user.pseudo;
	nc.call("requestHand",null,_root.user.pseudo);


}
/*
toggles  the color Palette  panel when user click the color  button
**/
function showPublicColorPalette() {
	_root.Application.colorPublicPalette._visible=!(_root.Application.colorPublicPalette._visible);
}
function showPrivateColorPalette() {
	_root.Application.colorPrivatePalette._visible=!(_root.Application.colorPrivatePalette._visible);
}

/*
toggles  the smiley panel when user click the smiley button
**/
function showSmileyPublicPanel() {
	_root.Application.publicSmileyPanel._visible=!(_root.Application.publicSmileyPanel._visible);
}
function showSmileyPrivatePanel() {
	_root.Application.privateSmileyPanel._visible=!(_root.Application.privateSmileyPanel._visible);
}
/*
changes the background color to bgColor in the user list to display that an private message was sent by an user who
**/
function setBackgroundUserColor(who:String,bgColor:String) {
	if (_root.people_grd.enabled==false) return;
	trace("setBackgroundUserColor who:"+who+" bg="+bgColor);
	for (i=0; i<_root.Application.people_grd.length; i++) {
		if (_root.Application.people_grd.getItemAt(i).username == who) {
			// change only if NOT already selected !
			if (_root.Application.people_grd.selectedItem.pseudo==who) return;
			_root.Application.people_grd.setPropertiesAt(i, {backgroundColor:bgColor});
			trace("backgournd "+i+" set to:"+bgColor);
			return;
		}
	}
}
/*
called when an user receives a private message from another user
**/
function receivePrivateMsg(fromPseudo:String, msg:String) {
	if (isUserInIgnoreList(fromPseudo)) return;
	if (privateMsg[fromPseudo]==undefined) privateMsg[fromPseudo]="";
	privateMsg[fromPseudo]=privateMsg[fromPseudo]+msg+"<br>";
	trace("received privateMsg[fromPseudo]="+privateMsg[fromPseudo]);
	_root.Application.historyPrivate.parseString(msg);
	if (_root.individualPrivateMessages==true) {
		if (_root.Application.people_grd.selectedItem.pseudo!=fromPseudo) {
			setBackgroundUserColor(fromPseudo,"0xFF0000");
			}
	} 
}

function selectUserInPeopleGrid() {
	
	item=_root.Application.people_grd.selectedItem;
	pseudo=item.label;
	hisStatus=item.onlineStatus;
	trace("hisStatus="+hisStatus);
	if ((_root.debug==true) || (hisStatus=="true" && pseudo!=_root.user.pseudo)) 
	{	_root.Application.private_btn._visible=true;
	} else {
		_root.Application.private_btn._visible=false;
	}
	_root.Application.privateChat_txt.text="private chat with "+pseudo;
	setBackgroundUserColor(pseudo,"0xFFFFFF");
	_root.Application.people_grd.redraw(true);
	// each messages is individual ?
	if (_root.individualPrivateMessages==true) {
		trace("private");
		if (privateMsg[pseudo]==undefined) {
			privateMsg[pseudo]="";
			return;
		}	
		_root.Application.historyPrivate.clearText();		
		_root.Application.historyPrivate.parseString(privateMsg[pseudo]);
		//individualChat[pseudo]=_root.Application.historyPrivate.
	}


}
function findNextWebcam() {
	if (_root.nextCam==undefined) _root.nextCam=0;
	_root.nextCam++;
	if (_root.nextCam==5) _root.nextCam=1;
	locked1=_root.Application.av4.lock1.lockedVideo;
	locked2=_root.Application.av4.lock2.lockedVideo;
	locked3=_root.Application.av4.lock3.lockedVideo;
	locked4=_root.Application.av4.lock4.lockedVideo;
	
	if ((_root.nextCam==1) && (locked1==true))  _root.nextCam=2;
	if ((_root.nextCam==2) && (locked2==true))  _root.nextCam=3;
	if ((_root.nextCam==3) && (locked3==true))  _root.nextCam=4;
	if ((_root.nextCam==4) && (locked4==true))  _root.nextCam=1;
	return _root.nextCam;
	
}
function playUserVideo(username) {
	if ((_root.Application.av2._visible==true)) {
		_root.Application.av2.outgoing._visible=true;
		if (_root.Application.lockUnlock_btn.lockedVideo==true) return;
		_root.in_stream = new NetStream(nc);
		_root.in_stream.onStatus = function(info:Object) {      
			trace ("*in_stream:"+info.code);
		}
		
		trace("playUserVideo:"+username);
		
		_root.Application.av2.outgoing.attachVideo(_root.in_stream);
		_root.Application.av2.attachAudio(_root.in_stream);
		_root.privateAudio_sound = new Sound(_root.Application.av2);
		_root.privateAudio_sound.setVolume(100);
		_root.in_stream.play(username);

		
		_root.Application.av2.pseudo_txt.text=username;
		

	} else {
		// display in 4 webcams !
		// search next webcam 
		activeCam=findNextWebcam();
		if (activeCam==1) {
			_root.in_stream1 = new NetStream(nc);
			_root.Application.av4.video1._visible=true;
			_root.Application.av4.video1.attachVideo(_root.in_stream1);	
			_root.Application.av4.clip1.attachAudio(_root.in_stream1);
			var audio_sound:Sound = new Sound(_root.Application.av4);
			audio_sound.setVolume(0);		
			_root.in_stream1.play(username);
			_root.Application.av4.pseudo1_txt.text=username;
			_root.Application.av4.video1.busy=true;
		}
		if (activeCam==2) {
			_root.in_stream2 = new NetStream(nc);
			_root.Application.av4.video2._visible=true;
			_root.Application.av4.video2.attachVideo(_root.in_stream2);
			_root.Application.av4.clip2.attachAudio(_root.in_stream2);
			var audio_sound:Sound = new Sound(_root.Application.av4);
			audio_sound.setVolume(0);			
			_root.in_stream2.play(username);
			_root.Application.av4.pseudo2_txt.text=username;
			_root.Application.av4.video2.busy=true;
		}
		if (activeCam==3) {
			_root.in_stream3 = new NetStream(nc);
			_root.Application.av4.video3._visible=true;
			_root.Application.av4.video3.attachVideo(_root.in_stream3);	
			_root.Application.av4.clip3.attachAudio(_root.in_stream3);
			var audio_sound:Sound = new Sound(_root.Application.av4);
			audio_sound.setVolume(0);				
			_root.in_stream3.play(username);
			_root.Application.av4.pseudo3_txt.text=username;
			_root.Application.av4.video3.busy=true;
		}
		if (activeCam==4) {
			_root.in_stream4 = new NetStream(nc);
			_root.Application.av4.video4._visible=true;
			_root.Application.av4.video4.attachVideo(_root.in_stream4);	
			_root.Application.av4.clip4.attachAudio(_root.in_stream4);
			var audio_sound:Sound = new Sound(_root.Application.av4);
			audio_sound.setVolume(0);				
			_root.in_stream4.play(username);
			_root.Application.av4.pseudo4_txt.text=username;
			_root.Application.av4.video4.busy=true;
		}		
			
			
	}

}


/*
function called when an user receives a public message msg from an user fromUser
**/
function receivePublicMsg(fromUser:String,msg:String) {
	_root.Application.historyPublic.parseString("<b>"+fromUser+":</b> "+msg);	
	
}
function removeUser(id,pseudo,room,world:String) {
	displayServerMessages(pseudo+" has left the chat");
	// test if the removed user is the one that was talking !
	if (_root.whoIsSpeaking==pseudo) {
		nc.call("stopTalk",null,pseudo);
	}
	if (_root.privateChatterID==id) {
		// end private converstaion
		stopPrivateInvitation();
	}
	for (i=0;i<_root.Application.people_grd.length;i++) {
		if (_root.Application.people_grd.getItemAt(i).label==pseudo) {
			_root.Application.people_grd.removeItemAt(i);
			updateNumberUsers();
			return;
		}
	}
}
function sendPrivateMessage() {
	var msg=_root.Application.msgPrivate_txt.text;
	if (msg=="") return;
	item=_root.Application.people_grd.selectedItem;
	if (item==undefined) {
		showHint("Select an user from the left to send a private chat !",0,450);
		return;
	}		
	_root.Application.help_mc._visible=false;
	_root.Application.msgPrivate_txt.text="";
	destinationID=item.id;
	destinationPseudo=item.label;
	fromPseudo=_root.user.pseudo;
	msg="<b>"+fromPseudo+":</b><font color='"+_root.colorPrivateText+"'>"+msg+"</font>";
	privateMsg[destinationPseudo]=privateMsg[destinationPseudo]+msg+"<br>";
	_root.Application.historyPrivate.parseString(msg);	
	nc.call("send_private",null,fromPseudo,destinationID,msg);
	
}
	
function sendPublicMessage() {
	var msg=_root.Application.msgPublic_txt.text;
	if (msg=="") return;
	msg="<font color='"+_root.colorText+"'>"+msg+"</font>";
	_root.Application.msgPublic_txt.text="";
	//trace("_root.user.pseudo="+_root.user.pseudo);
	//trace("_root.pseudo="+_root.pseudo);
	nc.call("send_public",null,_root.user.pseudo,msg);
	
}
function NewUserjoin(id,pseudo,webcam, onlineStatus,role,sex,room,world:String) {
	displayServerMessages(pseudo+" has joined the chat");
	users[pseudo].id=id;
	users[pseudo].pseudo=pseudo;
	users[pseudo].webcam=webcam;
	users[pseudo].onlineStatus=onlineStatus;
	users[pseudo].role=role;
	users[pseudo].sex=sex;
	users[pseudo].room=room;
	users[pseudo].world=world;
	// display it in the users list !
	
	_root.Application.people_grd.addItem({label:pseudo,username:pseudo, webcam:webcam, id:id, data:pseudo, sex:sex,role:role,onlineStatus:onlineStatus});
	updateNumberUsers();
}
function getUserList() {
	trace("getUserList called");
	function getUserListCallBack() {
		trace("getUserListCallBack called");
		this.onResult = function(_users) {
			for (_user in _users) {
			trace("pseudo:"+_users[_user].pseudo+" webcam:"+_users[_user].webcam);
				_root.Application.people_grd.addItem({label:_users[_user].pseudo,webcam:_users[_user].webcam,username:_users[_user].pseudo, data:_users[_user].pseudo, sex:_users[_user].sex, role:_users[_user].role, onlineStatus:_users[_user].onlineStatus,id:_users[_user].id});
				if (_root.user.pseudo==_users[_user].pseudo) _root.user.id=_users[_user].id;
			}
			updateNumberUsers();
		};
		
	}
	nc.call("getUserList", new getUserListCallBack());
}

function updateNumberUsers() {
	_root.Application.numberChatters_txt.text="online:"+_root.Application.people_grd.length;
}

function quit_chat() {
	nc.close;
	disable_GUI();
} 

function displayServerMessages(msg) {
	if (_root.serverMessages==false) return;
	_root.Application.historyPublic.parseString("<font color='"+colorTextInfo+"'>"+msg+"</font>");

} 
function setSizeText(sizeText) {
	var privateTemp=_root.Application.historyPrivate.htmlText;
	var publicTemp=_root.Application.historyPublic.htmlText;
	trace("privateTemp="+privateTemp);
	trace("publicTemp="+publicTemp);
	
	_root.Application.historyPublic._size = sizeText;
	_root.Application.historyPrivate._size = sizeText;
	_root.Application.historyPublic.init();
	_root.Application.historyPrivate.init();
	_root.Application.historyPrivate._width=322;
	_root.Application.historyPrivate._height=360;
	_root.Application.historyPublic._width=498;
	_root.Application.historyPublic._height=644;

	
	_root.Application.historyPublic.parseString(publicTemp);
	_root.Application.historyPrivate.parseString(privateTemp);
}

function maximizeWebcam1() {
	_root.Application.av2.outgoing.attachVideo(_root.in_stream1);
	webcam4Click();
	
}
function maximizeWebcam2() {
	_root.Application.av2.outgoing.attachVideo(_root.in_stream2);
	webcam4Click();
	
}
function maximizeWebcam3() {
	_root.Application.av2.outgoing.attachVideo(_root.in_stream3);
	webcam4Click();
	
}
function maximizeWebcam4() {
	_root.Application.av2.outgoing.attachVideo(_root.in_stream4);
	webcam4Click();
	
}




function initMenu() {
	_root.Application.myMenuBar.initiated=true;
	var menu1 = _root.Application.myMenuBar.addMenu("My status");
	var menu2 = _root.Application.myMenuBar.addMenu("Rooms");
	var menu = _root.Application.myMenuBar.addMenu("Parameters");
	
	
	menu1.addMenuItem({type:"check", selected:_root.online, label:"OnLine", instanceName:"online"});
	menu2.addMenuItem({label:"Change Rooms", instanceName:"changeRoom"});
	
	
	menu.addMenuItem({label:"Show my webcam", instanceName:"showMyWebcam"});	
	menu.addMenuItem({type:"check", selected:_root.listenToTalk, label:"Listen to Talks", instanceName:"listenToTalk"});	
	menu.addMenuItem({type:"check", selected:_root.playSounds, label:"Sounds", instanceName:"sounds"});
	menu.addMenuItem({type:"check", selected:_root.serverMessages, label:"Server messages", instanceName:"serverMessages"});	
	menu.addMenuItem({type:"check", selected:_root.individualPrivateMessages, label:"Individual private messages", instanceName:"individualPrivateMessages"});	

	
	trace("_root.user.role="+_root.user.role);
	if (_root.user.role=="a") menu.addMenuItem({label:"Admin tools", instanceName:"admintools"});
	var menulisten = new Object();
	menu.addEventListener("change", menulisten);
	menu1.addEventListener("change", menulisten);
	menu2.addEventListener("change", menulisten);

	
	menulisten.change = function(evt) {
		var menu = evt.menu;
		var item = evt.menuItem;
			if (item.attributes.instanceName == "changeRoom") {
			init_rooms();			
		}	
		if (item.attributes.instanceName == "listenToTalk") {
			_root.listenToTalk=item.attributes.selected;
			if (_root.listenToTalk==true) closeTalk();
				_root.Application.talk_btn._visible=item.attributes.selected;
				_root.Application.askHand_mc._visible=item.attributes.selected;
		}			
		if (item.attributes.instanceName == "online") {
			_root.user.onlineStatus=item.attributes.selected;	
			changeMyProfil();
		}	
		if (item.attributes.instanceName == "showMyWebcam") {
			_root.Application.av1._visible=true;
		}		

		if (item.attributes.instanceName == "sounds") {
			_root.playSounds=item.attributes.selected;			
		}
		if (item.attributes.instanceName == "serverMessages") {
			_root.serverMessages=item.attributes.selected;
		}
		if (item.attributes.instanceName == "individualPrivateMessages") {
			_root.individualPrivateMessages=item.attributes.selected;
			trace("_root.individualPrivateMessages="+_root.individualPrivateMessages);
		}				
		if (item.attributes.instanceName == "admintools") {
			_root.Application.admintools_window._visible=true;
		}		
		
	}	
}

function init_rooms() {
	login_msg("");
	_root.Application._visible=false;
	_root.login._visible=false;
	TransitionManager.start(rooms_mc, {type:Squeeze, direction:Transition.IN, duration:2, easing:Elastic.easeOut, dimension:1});
}


function init_chat() {
// if autoconnect =true, make sure to provide these parameters:
// pseudo=nickname, status= online, offline..., role=normal, admin..., sexe=m,f,a, room = name of room, world = world !
_root.login.sex_mc._visible=false;
	if (_root.authentificate==false) {
		_root.login.password_txt._visible=false;
		_root.login.password_label._visible=false;
		_root.login.sex_mc._visible=true;
		_root.Application.info_btn._visible=false;
		if (_root.pseudo==undefined) _root.pseudo="";
		if (_root.sexe==undefined) _root.sexe="m";
		if (_root.sexe=="m") {
			_root.login.male_rb.selected=true;
		} else {
			_root.login.female_rb.selected=true;
		}
		_root.login.login_txt.text=_root.pseudo;
	}
	TransitionManager.start(login, {type:Squeeze, direction:Transition.IN, duration:2, easing:Elastic.easeOut, dimension:1});
//	nc.connect(_root.rtmpString,"connectTemp");
	trace("connecting as connectTemp");
	disable_GUI();
	initPeopleGrid();
	if (_root.autoConnect==true) {
		//_root.login._visible=false;
		TransitionManager.start(login, {type:Squeeze, direction:1, duration:1, easing:Elastic.easeIn, dimension:1});
		login_chat();	
		} else {
		//no auto connect
		}
}
function checklogin(pseudo) {
	if (pseudo.length<2) return false;
	if (pseudo.length>20) return false;
for (i=0; i<pseudo.length-1; i++) {
	var c = pseudo.charAt(i);
	x=(ord(c));
	if (x<=32)  return false;
	if (x>122)  return false;}
return true	
}


_root.login.login_pb.onPress=function() {	
	_root.pseudo=_root.login.login_txt.text;
	_root.password=_root.login.password_txt.text;
	if (checklogin(_root.pseudo)) {
		login_chat();
	}
}


function login_chat() {
//	_root.login.login_pb.enabled=false;
	_root.user=new User(_root.pseudo,_root.webcam,_root.password,"true",_root.role,_root.sex,_root.room,_root.world);
	//trace("_root.user.onlinseStatus="+_root.user.onlineStatus);
	//trace("_root.user.pseudo="+_root.user.pseudo);
	if (_root.user.pseudo=="") {
		login_msg("UserName is not valid !");
		_root.login.login_pb.enabled=true;
		return;
	}
		
	if (_root.authentificate==true) {	

		if (_root.user.password=="") {
			login_msg("Password is not valid !");
			_root.login.login_pb.enabled=true;
			return;
		}
		login_msg("");	
		
		// authetificate : please check _root.authentificateURL and testlogin.php
		var myVars = new LoadVars();
		myVars.pseudo = _root.user.pseudo;
		myVars.password = _root.user.password;	
		//trace("*pseudo:"+myVars.pseudo+" pass="+myVars.password);
		//trace("authentificateURL="+_root.authentificateURL);
		myVars.sendAndLoad(_root.authentificateURL, myVars, "");
		myVars.onLoad = function(success) {
		trace("myVars.status="+myVars.status);
			if (myVars.status=="ok") {
				_root.user.sex=myVars.sex;
				_root.user.role=myVars.role;
				//trace("myVars.sex="+myVars.sex);
				connect_chat();
			} else  {
				login_msg("Incorrect User or password ");
				_root.login.login_pb.enabled=true;
				return;
			}
		} 
	} else {
			// no authentification !
			if (_root.role!="a") _root.role="n";
			_root.user.role=_root.role;
			_root.user.sex="m";
			if (_root.login.female_rb.selected==true) _root.user.sex="f";
			connect_chat();
			}
}	
function connect_chat() {
	//trace("connecting to chat");
	//params = 	 pseudo status role sexe  room world
	//trace(_root.rtmpString+"-"+_root.user.pseudo+"-"+_root.user.status+"-"+_root.user.role+"-"+_root.user.sex+"-"+_root.user.room+"-"+_root.user.world);

	_root.user.webcam=!(Camera.get().muted);
	trace("_root.user.webcam="+_root.user.webcam);
	nc.connect(_root.rtmpString,_root.user.pseudo,_root.user.webcam,_root.user.onlineStatus,_root.user.role,_root.user.sex,_root.user.room,_root.user.world);
	nc.onStatus = function(info){
	//trace("info.code="+info.code);
    if (info.code == "NetConnection.Connect.Success") {
        enter_chat();
    }  else if(info.code=="NetConnection.Connect.Rejected") {
		login_msg("UserName already in use !");
		_root.login.login_pb.enabled=true;
		return;
	} else {
		//login_msg("**UserName already in use !");
		_root.login.login_pb.enabled=true;
        return;
    }
};

}
 
function enter_chat() {
	if(_root.Application.myMenuBar.initiated!=true) initMenu();
	getUserList(); // get list of connected users
	//_root.login._visible=false;
	TransitionManager.start(login, {type:Squeeze, direction:1, duration:1, easing:Elastic.easeIn, dimension:1});
	enable_GUI();
	publishMyWebcam();
	
}

function login_msg(msg) {
	_root.login.loginStatus_txt.text=msg;
}

function set_cookies() {
	cookie = SharedObject.getLocal("config");
	if (cookie.data.banni == 1) {
		trace("I am banned");
		_root.loadMovie("banni.swf");
	}
}	


function isUserInIgnoreList(who) {
	for (i=0; i<ignore_list.length; i++) {
		if (who == ignore_list[i]) {
			return true;
		}
	}
	return false;
}
function ignoreUser() {	
	who=_root.Application.people_grd.selectedItem.username;
	
	if (isUserInIgnoreList(who) == false) {
		ignore_list.push(who);
		displayServerMessages(who +" is now ignored");		
		trace("ignore displayServerMessages:"+who);
	}
}

function disable_GUI() {
	_root.Application.privateChatMask_mc._visible=false;
	_root.Application.privateChatMask_mc.useHandCursor=false;
	_root.Application.privateCacheHeader_mc._visible=false;
	_root.Application._visible=true;
	_root.login._visible=true;
	
	_root.online=true;
	_root.Application.lockUnlock_btn._visible=false;
	_root.Application.av4._visible=false;
	_root.Application.tooltip_mc._visible = false;
	_root.Application.login.room_cmb.selectedIndex = 0;
	
	_root.Application.publicSmileyPanel._visible = false;
	_root.Application.smileyPublic_btn.enabled = false;
	_root.Application.privateSmileyPanel._visible = false;
	_root.Application.smileyPrivate_btn.enabled = false;
	
	_root.Application.wizzpanel._visible = false;	
	_root.Application.colorPublic_btn.enabled = false;
	_root.Application.colorPublicPalette._visible = false;
	_root.Application.colorPrivate_btn.enabled = false;	
	_root.Application.colorPrivatePalette._visible = false;
	_root.Application.help_mc._visible=false;
	_root.Application.window_mc._visible=false;
	_root.Application.info_btn.enabled=false;
	
	
	
	_root.Application.window_ban._visible = false;
	_root.Application.admintools_window._visible = false;
	_root.Application.handAsked_mc._visible = false;
	_root.Application.askHand_mc.enabled = false;
	_root.Application.av2.spin._visible = false;
	_root.Application.myMenuBar.enabled = false;
	_root.Application.infos_btn.enabled = false;
	_root.Application.msgPrivate_txt.enabled = false;
	_root.Application.msgPublice_txt.enabled = false;	
	_root.Application.send_pb.enabled = false;
	_root.Application.sendprive_pb.enabled=false;
	_root.Application.people_lb.enabled = false;
	_root.Application.historyPublic.enabled = false;
	_root.Application.historyPublic.clearText();
	_root.Application.msgPublic.text = "";
	_root.Application.av2.incoming._visible = false;
	_root.Application._alpha = 40;
	_root.Application.rooms_mc._visible = false;
	_root.Application.emotic_mc._visible = false;
	_root.Application.talk_btn.enabled = false;	
	_root.Application.webcam4_btn._visible=false;
	_root.webcams4=false;
	_root.Application.av1._visible=_root.showMyWebcam;
	_root.Application.private_btn._visible = false;
	_root.Application.window_invite._visible=false;
	_root.Application.endPrivate_btn._visible=false;
	_root.Application.closeVideo_btn._visible=false;
	//
	_root.Application.av4.max1_btn._visible=false;
	_root.Application.av4.lock1._visible=false;
	_root.Application.av4.max2_btn._visible=false;
	_root.Application.av4.lock2._visible=false;
	_root.Application.av4.max3_btn._visible=false;
	_root.Application.av4.lock3._visible=false;
	_root.Application.av4.max4_btn._visible=false;
	_root.Application.av4.lock4._visible=false;
	_root.Application.av4.close1_btn._visible=false;
	_root.Application.av4.close2_btn._visible=false;
	_root.Application.av4.close3_btn._visible=false;
	_root.Application.av4.close4_btn._visible=false;
	_root.Application.window_talk._visible=false;
	_root.Application.acceptCheckbox.enabled=false;
	
	
	
};
function enable_GUI() {
	_root.online=true;
	_root.Application.av2.outgoing.smoothing=true;
	_root.Application.info_btn.enabled=true;
	_root.Application.login.room_cmb.selectedIndex = 0;
	_root.Application.smileyPublic_btn.enabled = true;
	_root.Application.smileyPrivate_btn.enabled = true;
	_root.Application.colorPublic_btn.enabled = true;
	_root.Application.colorPrivate_btn.enabled = true;	
	_root.Application.askHand_mc.enabled = true;
	_root.Application.myMenuBar.enabled = true;
	_root.Application.infos_btn.enabled = true;
	_root.Application.msgPrivate_txt.enabled = true;
	_root.Application.msgPublice_txt.enabled = true;	
	_root.Application.send_pb.enabled = true;
	_root.Application.sendprive_pb.enabled=true;
	_root.Application.people_lb.enabled = true;
	_root.Application.historyPublic.enabled = true;
	_root.Application.talk_btn.enabled = true;		
	_root.Application.private_btn.enabled = true;
	_root.Application._alpha = 100;
	_root.Application.acceptCheckbox.enabled=true;
	
};
