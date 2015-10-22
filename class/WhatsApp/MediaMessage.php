<?php
	require_once dirname(__FILE__) . '/../Lib/_Loader.php';

	require_once dirname(__FILE__) . '/Message.php';

	class MediaMessage extends Message
	{
		public $SubType = null;

		public $URL = null;
		public $File = null;
		public $Size = null;

		public $MIME = null;
		public $Hash = null;

		protected $Data = null;

		protected $MediaDirectory = 'media';

		protected $PreviewFilenameSuffix = 'preview';

		public function __construct($Me, $From, $User, $ID, $Type, $Time, $Name, $SubType, $URL, $File, $Size, $MIME, $Hash)
		{
			$this->SubType = $SubType;
			$this->URL = !empty($URL) ? $URL : null;
			$this->File = $File; // It should be Filename => WhatsBot 2.2/3
			$this->Size = (int) $Size;
			$this->MIME = $MIME;
			$this->Hash = $Hash;

			parent::__construct($Me, $From, $User, $ID, $Type, $Time, $Name);

			$this->LoadData();
			$this->LoadPreview();
		}

		private function LoadData()
		{
			if(!empty($this->File) && isset($this->Data))
			{
				$this->Data = Data::Get($this->File, false, false, array($this->MediaDirectory));

				if(empty($this->Data))
				{
					if(!empty($this->URL))
					{
						$this->Data = file_get_contents($this->URL);

						if(!empty($this->Data))
							return Data::Set($this->File, $this->Data, false, true, array($this->MediaDirectory));
					}

					$this->Data = null;

					return false;
				}

				return true;
			}

			return false;
		}

		private function LoadPreview()
		{
			if(isset($this->Preview))
			{
				if(!empty($this->PreviewFilenameSuffix))
				{
					$Filename = pathinfo($this->File, PATHINFO_FILENAME) . '.' . $this->PreviewFilenameSuffix;

					$Extension = pathinfo($this->File, PATHINFO_EXTENSION);

					if(!empty($Extension))
						$Filename .= '.' . $Extension;
				}
				else
					$Filename = $this->File;

				$Preview = Data::Get($Filename, false, false, array($this->MediaDirectory));

				if(!empty($Preview))
				{
					if(empty($this->Preview))
						$this->Preview = $Preview;

					return true;
				}
				elseif(!empty($this->Preview))
					return Data::Set($Filename, $this->Preview, false, true, array($this->MediaDirectory));
			}

			return false;
		}

		public function GetData() // It's binary data, StorageListener can't log it (json_encode warning)
		{ return $this->Data; }

		public function GetType()
		{ return $this->SubType; }
	}