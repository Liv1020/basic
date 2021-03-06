<?php

class DefaultController extends YController {

	/**
	 * Declares class-based actions.
	 */
	public function actions() {
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha' => array(
				'class' => 'CCaptchaAction',
				'backColor' => 0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page' => array(
				'class' => 'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex() {
		$ads = Ad::model()->findAll();
		$posts = Post::model()->findAll(new CDbCriteria(array('limit' => 5, 'order' => 'id desc')));
		$hotCategories = Category::model()->hot()->level(2)->limit(3)->findAll();
		$hotItems = array();
		foreach ($hotCategories as $hotCategory) {
			$hotItems[$hotCategory->name] = Item::getItemsByCategory($hotCategory, 4);
		}
		$newCategories = Category::model()->new()->level(2)->limit(3)->findAll();
		$newItems = array();
		foreach ($newCategories as $key=>$newCategory) {
			if($key==0){
				$newItems[$newCategory->name] = Item::getItemsByCategory($newCategory, 7);
			}else
				$newItems[$newCategory->name] = Item::getItemsByCategory($newCategory, 8);
		}
		Yii::app()->params['ads'] = $ads;
//        $newItems[0] = array_slice($newItems[0], 7);
		$this->render('index', array(
			'ads' => $ads,
			'posts' => $posts,
			'hotCategories' => $hotCategories,
			'hotItems' => $hotItems,
			'newCategories' => $newCategories,
			'newItems' => $newItems,
		));

	}

	public function actionEmail() {
		$message = 'Hello World!';
		Yii::app()->mailer->Host = 'smtp.gmail.com';
		Yii::app()->mailer->IsSMTP();
		Yii::app()->mailer->SMTPAuth = true; //設定SMTP需要驗證
		Yii::app()->mailer->SMTPSecure = "ssl"; // Gmail的SMTP主機需要使用SSL連線
		Yii::app()->mailer->Port = 465;  //Gamil的SMTP主機的SMTP埠位為465埠。
		Yii::app()->mailer->CharSet = "big5"; //設定郵件編碼
		Yii::app()->mailer->Username = "yhxxlm@gmail.com"; //設定驗證帳號
		Yii::app()->mailer->Password = ""; //設定驗證密碼
		Yii::app()->mailer->From = 'yhxxlm@gmail.com';
		Yii::app()->mailer->FromName = 'yhxxlm';
		Yii::app()->mailer->AddReplyTo('yhxxlm@gmail.com');
		Yii::app()->mailer->AddAddress('yhxxlm@foxmail.com');
		Yii::app()->mailer->Subject = 'Yii rulez!';
		Yii::app()->mailer->Body = $message;
		Yii::app()->mailer->Send();
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError() {
		if ($error = Yii::app()->errorHandler->error) {
			if (Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact() {
		$model = new ContactForm;
		if (isset($_POST['ContactForm'])) {
			$model->attributes = $_POST['ContactForm'];
			if ($model->validate()) {
				$headers = "From: {$model->email}\r\nReply-To: {$model->email}";
				mail(Yii::app()->params['adminEmail'], $model->subject, $model->body, $headers);
				Yii::app()->user->setFlash('contact', 'Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact', array('model' => $model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin() {
		$model = new LoginForm;
		// if it is ajax validation request
		if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		// collect user input data
		if (isset($_POST['LoginForm'])) {
			$model->attributes = $_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if ($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
		$this->render('login', array('model' => $model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout() {
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}

	public function actionCart() {
		$this->render('cart');
	}

	public function actionClear() {
		if(Yii::app()->cache->flush()){
			echo 'Yes';
			$this->redirect('/site');
		}else{
			echo 'No';
			$this->redirect('/site');
		}
	}

}