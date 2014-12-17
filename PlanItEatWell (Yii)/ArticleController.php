<?php

class ArticleController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index','view'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('create','update','UploadFileEAjaxUploader','myarticles','myfavoritearticles','deletemyarticle','addfavoritearticle','deletefavoritearticle','smartsearch','addrating'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','delete','updatearticlestatus'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new Article;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Article']))
		{
                        $_POST['Article']['user_id'] = Yii::app()->user->id;
                        $_POST['Article']['created_on'] = date("Y-m-d H:i:s");
                        $_POST['Article']['updated_on'] = date("Y-m-d H:i:s");

			$model->attributes=$_POST['Article'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Article']))
		{
                        if(Yii::app()->user->id==1)
                        {
                            //$_POST['Article']['user_id'] = Yii::app()->user->id;
                        }
                        else
                        {
                            $_POST['Article']['user_id'] = Yii::app()->user->id;
                        }

                        $_POST['Article']['updated_on'] = date("Y-m-d H:i:s");

                        $model->attributes=$_POST['Article'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id, $forUserArticle=null)
	{
                //echo 'Delete CALLED.';exit;

                /*
                 * Delete an Article by deleting  FIRST:
                 * Ratings,
                 * Favorites,
                 * Image,
                 * Comments
                */

		$this->loadModel($id)->delete();

                if($forUserArticle)
                {
                    return TRUE;        //For USer Recipe Delete Function Only
                }

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('Article');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Article('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Article']))
			$model->attributes=$_GET['Article'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Article the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Article::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Article $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='article-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}

        public function actionUploadFileEAjaxUploader()
        {
                Yii::import("ext.EAjaxUpload.qqFileUploader");

                $folder=Yii::getPathOfAlias('webroot').'/assets/article-images/';// folder for uploaded files
                $allowedExtensions = array("jpg");//array("jpg","jpeg","gif","exe","mov" and etc...
                $sizeLimit = 10 * 1024 * 1024;// maximum file size in bytes
                $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
                $result = $uploader->handleUpload($folder);
                $return = htmlspecialchars(json_encode($result), ENT_NOQUOTES);

                $fileSize=filesize($folder.$result['filename']);//GETTING FILE SIZE
                $fileName=$result['filename'];//GETTING FILE NAME
                
                /*
                $filenameParts = explode(".", $fileName);
                //$fileName = NULL;
                //rename ("/folder/file.ext", "/folder/newfile.ext");
                //$fileName = rename ($folder.$result['filename'], $folder.uniqid());
                //rename("/tmp/tmp_file.txt", "/home/user/login/docs/my_file.txt");
                //$result['filename'] = rename ($folder.$result['filename'], $folder.uniqid().".".$filenameParts[1]);//$fileName;//GETTING FILE NAME
                $fileName = rename ($folder.$result['filename'], $folder.uniqid().".".$filenameParts[1]);
                $result['filename'] = $fileName;
                 */

                echo $return;// it's array
        }

                
        public function actionAddFavoriteArticle()
        {
                //echo'actionAddToFavorite CALLED.';die();
                //echo'User ID'.Yii::app()->user->getId();die();
                //echo"Recipe Id: ".$_POST['recipe_id'];die();

                if(!Yii::app()->user->getId()){
                    //echo'Login Required.';die();
                    echo'Login Required!';die();
                }
                else{
                    //echo 'User Logged In.'.Yii::app()->user->id;die();
                    $model=new ArticleFavorite;
                    $_POST['ArticleFavorite']['user_id'] = Yii::app()->user->id;
                    $_POST['ArticleFavorite']['article_id'] = $_POST['articleId'];

                    $model->attributes=$_POST['ArticleFavorite'];

                    if($result=$model->findByAttributes(array('user_id'=>Yii::app()->user->id, 'article_id'=>$_POST['articleId'])))
                    {
                        //echo 'Recipe Found.';print_r($result);die();
                        echo'This Article is already in your Favorite List!';die();
                    }
                    elseif($model->save())
                    {
                        echo'1';die();
                    }
                }

        }
                
        function actionDeleteFavoriteArticle()
        {
                if(!Yii::app()->user->getId()){
                    //echo'Login Required.';die();
                    echo'Login Required!';die();
                }
                else{
                    //echo 'User Logged In.'.Yii::app()->user->id;die();

                    if($result =  ArticleFavorite::model()->findByAttributes(array('user_id'=>Yii::app()->user->id, 'id'=>$_POST['articleId'])))
                    {
                        //echo 'Recipe Found.';print_r($result);die();
                        //echo'This Article is already in your Favorite List!';die();
                        if(ArticleFavorite::model()->deleteByPk($result['id']))
                        {
                            echo'1';
                        }
                        else
                        {
                            echo'Article didn\'t deleted from your favorite list. Please try again!';
                        }
                    }
                    else
                    {
                        echo'This Article is not in your Favorite List!';die();
                    }
                }
            

        }

        function actionMyArticles()
        {
            $this->render('my_articles');
        }

        function actionDeleteMyArticle()
        {
            //echo 'actionDeleteMyRecipe CALLED.';exit;
            //echo 'recipeId: '.$_POST['recipeId'];

            $recipeObj = new Recipe();
            $recipe = null;
            $recipe = Article::model()->findByPk($_POST['articleId']);
            //print_r($recipe);exit;
            //echo'Created By: '.$recipe['created_by'];exit;
            if($recipe['user_id']==Yii::app()->user->id)
            {
                //if(Recipe::model()->deleteByPk($_POST['recipeId']))
                if($this->actionDelete($_POST['articleId'],1))
                    echo true;
                else
                    echo false;
            }
            else {
                echo false;
            }
        }

        function actionMyFavoriteArticles()
        {
            $this->render('my_favorite_articles');
        }

        // Article, smart search
        public function actionSmartSearch(){
            $article = new Article();
            $articles = NULL;
            $postData = array('article'=>'','category'=>NULL,'search'=>'basic');
            if($_POST){
                $postData = $_POST;
                
                if(isset($postData['search']) && $postData['search'] == "advanced"){
                    $category = NULL;
                    /*
                    if(isset($postData['category']) && $postData['category']!='' && sizeof($postData['category']) > 0){
                        // check if first selection is empty
                        if(isset($postData['category'][0]) && trim($postData['category'][0]) === ''){
                            unset($postData['category'][0]);
                        }
                        $category = implode(',', $postData['category']);
                        $category = $postData['category'];
                    }
                    //*/

                    $category = $postData['category'];
                    $articles = Article::model()->searchArticleCustom($postData['title'], $category);
 
                }
                else{
                    $articles = Article::model()->searchArticleCustom($postData['title']);
                }
                
            }
            
            $this->render('_search_article',array('model'=>$article,'articles'=>$articles,'postData'=>$postData));
            
        }

        public function actionAddRating()
        {
                //echo'actionAddRating CALLED.';die();
                //echo'User ID'.Yii::app()->user->getId();die();
                //echo"Rating Value: ".$_POST['rating'];die();
                //echo"Recipe Id: ".$_POST['recipe_id'];die();

                $model=new ArticleRating;
                $_POST['ArticleRating']['user_id'] = Yii::app()->user->id;
                $_POST['ArticleRating']['article_id'] = $_POST['article_id'];                
                $_POST['ArticleRating']['rating'] = $_POST['rating'];
                $_POST['ArticleRating']['created_on'] = date("Y-m-d H:i:s");

                $model->attributes=$_POST['ArticleRating'];
                        
                if($result=$model->findByAttributes(array('user_id'=>Yii::app()->user->id, 'article_id'=>$_POST['article_id'])))
                {
                    //print_r($result);die();
                    //echo $result['id'];die();
                    //echo'Your comments have been updated.';die();
                    if($model->updateByPk($result['id'],array('rating'=>$_POST['rating'], 'created_on'=>date("Y-m-d H:i:s"))))
                    {
                        echo'You have successfully updated your ratings to this Article.';die();
                    }
                }
                elseif($model->save())
                {
                    echo'You have successfully rated this Article.';
                }
        }

        function actionupdateArticleStatus(){
            //echo 'actionupdateArticleStatus CALLED!';exit();
            //echo 'Article ID: '.$_POST['articleId'];
            //echo 'Article Status: '.$_POST['articleStatus'];

            if(Article::model()->updateByPk($_POST['articleId'], array('status'=>$_POST['articleStatus'])))
            {
                echo true;
            }
            else
            {
                echo false;
            }
        }
}
