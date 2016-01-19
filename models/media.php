<?
/*--------------------------------------------------------- 
	class for interaction with the MEDIA table 

	fields
		+ blob
			- caption
		+ varchar
			- type
		+ int
			- id
			- active
			- object
			- rank
		+ float
			- weight
		+ datetime
			- created
			- modified
---------------------------------------------------------*/
class Media extends Model
{
	const table_name = "media";
}
?>