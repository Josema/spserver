package
{
	public interface ISynchable
	{
		function get isDirty():Boolean;
		function clearDirtyFlag():void;
		function get changedProperties():Object;
	}
}