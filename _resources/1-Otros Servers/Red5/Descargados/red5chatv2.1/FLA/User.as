class User { 
 // Define property names and types 
 public var id:String;
 public var pseudo:String; 
 public var webcam:String; 
 public var password:String;   
 public var onlineStatus:String;
 public var role:String;  
 public var sex:String; 
 public var room:String;
 public var world:String; 
 
 
 function User(pseudo,webcam,password,onlineStatus,role,sex,room,world) { 
 // Assign passed values to properties when new Plant object is created 
	this.pseudo = pseudo; 
	this.webcam=webcam;
	this.password = password; 	
	this.onlineStatus = "true";
	this.role = ""; 
	this.sex = sex; 
	this.room = "welcome"; 
	this.world = "world1"; 

 } 
  
}