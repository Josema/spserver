import mx.core.UIComponent;
class IconCellRenderer extends UIComponent {
	var icon_mc:MovieClip;
	var owner;
	// The row that contains this cell
	var listOwner;
	// the List/grid/tree that contains this cell
	var getCellIndex:Function;
	// the function we receive from the list
	var getDataLabel:Function;
	// the function we receive from the list
	var firstSizeCompleted:Boolean;
	// for mysterious initialization	
	function IconCellRenderer() {
	}
	function createChildren(Void):Void {
		firstSizeCompleted = false;
	}
	// note that setSize is implemented by UIComponent and calls size(), after setting
	// __width and __height
	function size(Void):Void {
		//invalidate();
	}
	function draw(Void):Void {
		if (icon_mc != undefined) {
			//icon_mc._x = (__width-icon_mc._width)/2;
		}
	}
	function setValue(str:String, item:Object, sel:Boolean):Void {
		// We're on an empty row
		if (item == undefined) {
			if (icon_mc != undefined) {
				icon_mc.removeMovieClip();
				delete icon_mc;
			}
			return;
		}
		if (icon_mc != undefined) {
			icon_mc.removeMovieClip();
		}
		// Attention au tri, il faut recalculer l'icone          
		var columnIndex = this["columnIndex"];
		// private property (no access function)
		var columnName = listOwner.getColumnAt(columnIndex).columnName;
		var iconFunction:Function = listOwner.getColumnAt(columnIndex).iconFunction;
		if (iconFunction != undefined) {
			var icon = iconFunction(item, columnName);
			if (icon != undefined) {
				if (columnName == "webcam") {
					if (item.webcam=="off") return;
					icon_mc = createObject(icon, item.username, 20);
					icon_mc.setSize(16, 16);
					icon_mc.icon = "webcam2";
					icon_mc.useHandCursor = true;
					icon_mc.onPress = function(eventObj:Object):Void  {
						trace("this._parent._parent._name="+this._parent._parent._parent._parent._name);
						if (this._parent._parent._parent._parent.enabled==false) return;
						trace("---");
						_root.playUserVideo(this._name);
					};
					function clicker() {
						//var target = eventObj.target;
						listOwner.selectedIndex = getCellIndex().itemIndex;
						listOwner.dispatchEvent({type:"cellEdit"});
						var name:String = listOwner.selectedItem.ProductName;
						trace("clicked"+listOwner.selectedIndex);
					}
				} else {
					icon_mc = createObject(icon, "icon_mc", 20);
					icon_mc._x = (__width-icon_mc._width)/2;
					icon_mc._y = (__height-icon_mc._height)/2;
				}
			}
		}
	}
	function getPreferredHeight(Void):Number {
		return owner.__height;
	}
}
