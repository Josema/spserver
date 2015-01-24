/*
 * RemoveCellRenderer
// by PhilFlash - http://philflash.inway.fr
 *
 * Dont't forget :
 * - to create a new symbol in Flash MX2004
 *   Insert > New Symbol
 *   with properties :
 *    Name : RemoveCellRenderer
 *    Behavior : MovieClip : Checked
 *   For Linkage:
 *    Identifier: RemoveCellRenderer
 *    AS 2.0 Class : RemoveCellRenderer
 *    Export for Actionscript : Checked
 *    Export for in first frame : Checked
 *
 * - to have a movie clip "trash" in your library
 *   With Linkage:
 *    Identifier: trash
 *    Export for Actionscript : Checked
 *    Export for in first frame : Checked
 *
 * - to have an Alert component in your library
 *
 */
import mx.core.UIComponent;
import mx.controls.Button;
class RemoveCellRenderer extends UIComponent {
	private var close:Button;
	var owner;
	// The row that contains this cell	
	var listOwner:MovieClip;
	// the reference we receive to the list
	var getCellIndex:Function;
	// the function we receive from the list
	var getDataLabel:Function;
	// the function we receive from the list
	function RemoveCellRenderer() {
	}
	function init(Void):Void {
		super.init();
	}
	function createChildren(Void):Void {
		trace("this="+this);
		close = mx.controls.Button(createObject("Button", "boutonwebcam", 1));
		close.setSize(24, 24);
		close.icon = "webcamIcon";
		close.addEventListener("click", this);
		close.useHandCursor=true;
	}
	function size(Void):Void {
		close._x = (__width-25)/2;
		close._y = (__height-25)/2;
	}
	function setValue(value:String, item:Object, sel:Boolean):Void {
		close._visible = (item != undefined);
	}

	//function getPreferredWidth :: only really necessary for menu
	function click(eventObj:Object):Void {
		var target = eventObj.target;
		listOwner.selectedIndex = getCellIndex().itemIndex;
		listOwner.dispatchEvent({type:"cellEdit"});
		var name:String = listOwner.selectedItem.ProductName;
		trace("clicked"+listOwner.selectedIndex);
	}
	// -----  -----
}
