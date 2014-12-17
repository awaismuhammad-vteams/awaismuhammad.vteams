<?php

/**
 * This is the model class for table "article".
 *
 * The followings are the available columns in table 'article':
 * @property integer $id
 * @property integer $category_id
 * @property integer $user_id
 * @property string $title
 * @property string $text
 * @property string $image
 * @property string $video_link
 * @property integer $status
 * @property string $created_on
 * @property string $updated_on
 *
 * The followings are the available model relations:
 * @property ArticleCategory $category
 * @property Users $user
 * @property ArticleFavorite[] $articleFavorites
 */
class Article extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'article';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('category_id, user_id, status', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>25),
			array('image, video_link', 'length', 'max'=>256),
			array('text, created_on, updated_on', 'safe'),
			array('category_id, title, text, user_id', 'required'),
                        array('video_link', 'url', 'defaultScheme' => 'http'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, category_id, user_id, title, text, image, video_link, status, created_on, updated_on', 'safe', 'on'=>'search'),
                        //array('verifyCode', 'captcha', 'on'=>'insert'),
                        //array('verifyCode', 'activeCaptcha', 'on'=>'active'),
		);
	}

        public function activeCaptcha()
        {
            $code = Yii::app()->controller->createAction('captcha')->verifyCode;
            if ($code != $this->verifyCode)
                $this->addError('verifyCode', 'Wrong code.');
        }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'category' => array(self::BELONGS_TO, 'ArticleCategory', 'category_id'),
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
			'articleFavorites' => array(self::HAS_MANY, 'ArticleFavorite', 'article_id'),
                        //'recipeRatings' => array(self::HAS_MANY, 'RecipeRating', 'recipe_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'category_id' => 'Article Category',
			'user_id' => 'User',
			'title' => 'Article Title',
			'text' => 'Article Text',
			'image' => 'Article Image',
			'video_link' => 'Video Link',
			'status' => 'Status',
			'created_on' => 'Created On',
			'updated_on' => 'Updated On',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('text',$this->text,true);
		$criteria->compare('image',$this->image,true);
		$criteria->compare('video_link',$this->video_link,true);
		$criteria->compare('status',$this->status);
		//$criteria->compare('created_on',$this->created_on,true);
		//$criteria->compare('updated_on',$this->updated_on,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Article the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

                
        public function getImage($image=NULL){
            //echo'***'.$image.'***';die();
            //echo 'Assets URL: '.Yii::app()->request->baseUrl.'/assets';exit;
            if($image == NULL){
                $image = $this->image;
            }
            //echo 'Image: '.$image;exit();
            
            $basePath = NULL;

            $basePath = Yii::getPathOfAlias('webroot').'/assets/article-images/'.$image;

            if($image && file_exists($basePath)){
                return $urlPath = Yii::app()->getBaseUrl(TRUE).'/assets/article-images/'.$image;
            }
            else{
                return $urlPath = Yii::app()->getBaseUrl(TRUE).'/assets/article-images/default.jpg';
            }
        }

        public function searchArticleCustom($title,$category=NULL)
        {
                $query = "SELECT a.id, a.title title, c.name category 
                            FROM article a 
                            LEFT JOIN article_category c ON a.category_id = c.id 
                        ";

                if($category == NULL){ // basic search for recipe
                        $query .= " WHERE a.title LIKE '%$title%' " ;
                }else{ 
                        $query .= " WHERE a.title LIKE '%$title%' ";
                        if($category != NULL){
                            $query .= "AND c.id in ($category)";
                        }
                }

                $query .= " AND status = 1" ;
                $query .= " GROUP BY a.id";
                $articles = Yii::app()->db->createCommand($query)->queryAll();

                return $articles;
        }
}
