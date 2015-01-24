class User { 
 // Define property names and types 
 public var id:String;
 public var pseudo:String; 
 public var status:String;
 public var role:String;  
 public var sexe:String; 
 public var room:String;
 public var world:String; 
 
 function User(id,pseudo,status,role,sexe,room,world) { 
 // Assign passed values to properties when new Plant object is created 
	this.id = id; 
	this.pseudo = pseudo; 
	this.status = status; 
	this.role = role; 
	this.sexe = sexe; 
	this.room = room; 
	this.world = world; 
	
 } 
  
}