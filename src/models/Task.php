<?php namespace djiney\crontab\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "task".
 *
 * @property integer $id
 * @property integer $date
 * @property string  $name
 *
 */
class Task extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return '{{%tasks}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['name'], 'string'],
			[['date'], 'safe']
		];
	}

	/**
	 * @param string $name
	 * @return bool|Task
	 */
	public static function get($name)
	{
		$task = self::find()
			->where(['name' => $name])
			->andWhere(['<', 'date', date('Y-m-d H:i:s')])
			->orderBy(['date' => SORT_ASC])
			->one();

		return ($task === null || !$task->claim()) ? false : $task;
	}

	/**
	 * @param null $name
	 * @return \yii\db\ActiveQuery
	 */
	public static function getList($name = null) : ActiveQuery
	{
		$list = self::find()
			->orderBy(['date' => SORT_ASC]);

		if ($name) {
			$list->where(['name' => $name]);
		}

		return $list;
	}

	/**
	 * @return bool
	 */
	public function claim() : bool
	{
		try {
			$count = $this->delete();
		} catch (\Throwable $e) {
			return false;
		}

		return $count === 1;
	}

	public function remove() : bool
	{
		try {
			return $this->delete() === 1;
		} catch (\Throwable $e) {
			return false;
		}
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	public static function create($data) : bool
	{
		$task = self::findOne($data);
		if ($task !== null) {
			return true;
		}

		$task = new self($data);
		return $task->save();
	}

	public static function getCount($name) : int
	{
		return self::find()
			->where(['name' => $name])
			->count();
	}

	public static function getLastDate(string $name)
	{
		$last = self::find()
			->where(['name' => $name])
			->orderBy(['date' => SORT_DESC])
			->one();

		return $last === null ? date('Y-m-d H:i:s') : $last->date;
	}

	public static function getNewTasks() : ActiveQuery
	{
		return self::find()
			->where(['<=', 'date', date('Y-m-d H:i:s')]);
	}
}
