![](/images/umeko-logo.png)

## 更新履歴 -Update
- 2023/10/4  サイトマップ自動生成機能のlocdateをW3C形式に修正
- 2023/6/30  サイトマップ自動生成機能を追加
- 2022/10/31 記事のプレビュー機能を追加
- 2022/9/5   TOPページのOGPタグmodified_dateを最後に更新された記事の更新日になるように修正
- 2022/6/28  本文中にscriptタグがある記事を再編集するときに一定の操作を行うと表示が崩れる問題を修正
- 2022/4/8   予約投稿記事の更新日が過去のものになるケースを修正
- 2022/3/3   自動目次機能を追加しました。

**<br>
- Octber 4th,2023    Corrected locdate of sitemap automatic generation function to W3C format.
- June 30th,2023     Added automatic sitemap generation function.
- Octber 31st,2022   Added article preview function.
- September 5th,2022 Corrected the OGP tag modified_date on the TOP page to be the updated date of the last updated article.
- June 28th,2022     Fixed a problem that the display collapsed when performing certain operations when re-editing an article with script tags in the text
- April 8th,2022     Fixed a case where the update date of the reserved post article was in the past.
- March 3rd,2022     Added automatic table of contents generation function.

## 梅子-Umeko-
梅子はウェブページを管理するCMSアプリケーションです。<br>
Naho Osadaによって開発されました。<br>
梅子は主にブログのような、「更新頻度がそれなりに高い」ものに向いています。<br>

**<br>
Umeko is a CMS application that manages web pages.<br>
Developed by Naho Osada.<br>
It is mainly suitable for things such as blogs that are "updated frequently".

## あなたが梅子-Umeko-を使うことで得られるメリット -Benefits you get from using Umeko
- インターネット上にあなたのブログを公開できる
- 強制更新がないので、安定して使い続けられる
- 更新があっても、それを適用するかはあなた自身が選択できる
- 複雑な機能がないので、新しく覚えることが少ない
- 機能の追加、編集ができる（PHP、HTML、CSSなどの基本的な知識があれば。Laravelの知識があると尚良いでしょう）
- MITライセンスなのであなたの責任範囲で自由に利用できる

**<br>
- You can publish your blog on the internet.
- Since there is no forced update, you can continue to use it stably.
- Even if there is an update, you can choose whether to apply it.
- There are no complicated functions, so there is little new learning.
- You can add and edit functions (if you have basic knowledge of PHP, HTML, CSS, etc., it is better to have knowledge of Laravel)
- It is an MIT license, you can use it freely within your responsibility.

## 梅子-Umeko-の開発者コメント -Developer comments
私事ですが、開発者の中高生時代（2000年頃です）から様々なブログサービスを使って作成しては破棄、を繰り返してきてました。<br>
初めて就職した会社がウェブサイト管理CMSを販売する会社で、そこのCMSの開発や別途納品するシステムの開発をしていました（すごく雑に言うとWordPressの日本企業版です）。<br>
子どもの頃から興味もあった「インターネット上に記事を書くこと」に加え、仕事のひとつとしても経験したことでいつかこれを自作してみたいと思っていました。<br>
その思いが形になったものが「梅子-Umeko-」です。<br>

**<br>
Personally, since I was a developer in middle and high school (around 2000), I have repeatedly created and destroyed blog services using various blog services.<br>
The company that got a job for the first time is a company that sells website management CMS, and was developing the CMS there and the system to be delivered separately (Very roughly speaking, it's the Japanese corporate version of WordPress).<br>
I was able to do "articles on the Internet" from the children's floor, and by experiencing the actual room, I was able to make my own.<br>
I wanted to make this one day, so I made it.

## 梅子-Umeko-の機能紹介 -Function introduction

### 管理画面ログイン -Management screen login
![](/images/login-sample.png)

登録したメールアドレス、パスワード、Captchaを入力してログインします。<br>
Captchaは<a href="https://github.com/mewebstudio/captcha" target="_blank">Captcha for Laravel 5/6/7(mewebstudio)</a>を使用しています（Thanks!）。

**<br>
Enter your registered email address, password and Captcha to log in.<br>
Captcha uses Captcha for Laravel 5/6/7 (mewebstudio) (Thanks!).

![](/images/login-top-sample.png)

ログイン後は最近更新された記事の一覧を表示します。<br>
ログイン権限は管理者と一般ユーザーがあります。<br>
管理者はすべての情報を操作できます。一般ユーザーは自分が登録した情報のみ操作ができます。<br>

**<br>
After logging in, a list of recently updated articles will be displayed.<br>
Login privileges are for administrators and general users.<br>
The administrator can manipulate all the information. General users can only operate the information that they have registered.<br>

### ユーザーの管理 -User management
ユーザー情報の管理をする機能です。

**<br>
It is a function to manage user information.

#### 管理者 -Administrator
![](/images/user-sample.png)

登録されているユーザーの確認、登録、削除ができます。<br>
この画面は管理者ユーザーのみの機能です。<br>

**<br>
You can check, register, and delete registered users.<br>
This screen is a function only for administrator users.<br>

#### 一般ユーザー -General user
![](/images/user-normal-sample.png)

一般ユーザーの場合は自分の情報のみ閲覧できます。<br>
試験用データで一旦ログインし、ユーザー画面から自分が使用する管理者ユーザーを登録します。<br>
一旦ログアウトして、登録したユーザーでログインします。あとはそのユーザーで不要なデフォルトデータを削除することで使用できるようになります。<br>

**<br>
If you are a general user, you can only view your own information.<br>
Log in once with the test data, and register the administrator user you use from the user screen.<br>
Log out and log in as the registered user. After that, it can be used by deleting unnecessary default data for that user.

### 記事の管理 -Article management
梅子の最重要の機能です。公開サイトのページ情報を管理します。

**<br>
This is the greatest important function of Umeko. Manage page information for public sites.<br>

#### 記事の一覧 -List of articles
![](/images/post-list-sample.png)

登録されている記事の一覧が確認できます。<br>
管理者はすべての記事、一般ユーザーは自分で書いた記事のみ表示されます。<br>
この画面から公開ステータスの変更ができます。<br>

**<br>
Administrators can see all articles, and general users can only see articles written by themselves.<br>
You can change the publishing status from this screen.<br>

#### 記事の投稿、編集 -Posting and editing articles
![](/images/post-sample.png)

梅子の最大の機能、公開する記事の登録、編集、削除ができます。<br>
エディタは<a href="https://alex-d.github.io/Trumbowyg/">Trumbowyg</a>を使用しています（Thanks!）。<br>
画像のアップロードも可能です。<br>
公開ステータスは公開中、非公開、下書きの3種類があります。公開中ステータスのもののみ、ログインなしでアクセスできます。<br>
記事のURLは「/公開年/公開月/ページ名称」でアクセスします。<br>
また、記事の登録、編集、削除をするとサイトマップが自動で生成されます。<br>

**<br>
Umeko's greatest function, you can register, edit, and delete articles to be published.<br>
The editor uses Trumbowyg (Thanks!).<br>
Images can also be uploaded.<br>
There are three types of public status: public, private, and draft. Only those with public status can be accessed without logging in.<br>
The URL of the article is accessed by "/ year of publication / month of publication / page name".<br>
Also, when you register, edit, or delete an article, a sitemap is automatically generated.<br>

### カテゴリー -Category
記事に付属するカテゴリーを登録することができます。ただ、こちらは使用しなくても問題はありません。<br>
カテゴリーの登録があるときは記事の投稿、編集画面にカテゴリーが表示されます。複数選択が可能です。<br>

**<br>
You can register the categories attached to the article. However, there is no problem even if you do not use this.<br>
When the category is registered, the category will be displayed on the post / edit screen of the article. Multiple selections are possible.<br>

#### 一覧 -List of categories
![](/images/category-sample.png)

登録されているカテゴリーの一覧です。<br>
管理者は削除ができます。<br>
ユーザーは自分が作成したカテゴリーの編集のみができます。自分以外のユーザーが作成したカテゴリーは表示のみされます。<br>

**<br>
It is a list of registered categories.<br>
The administrator can delete it.<br>
Users can only edit the categories they create. Categories created by users other than yourself are only displayed.<br>

#### カテゴリーの投稿、編集 -Posting and editing categories
![](/images/category-edit-sample.png)

カテゴリーの投稿、編集ができます。<br>
表示名はサイトに表示する名称、カテゴリー名はアクセスする際などに使用する内部名称です。<br>
カテゴリーの表示順もここで設定できます。<br>

**<br>
You can post and edit categories.<br>
The display name is the name displayed on the site, and the category name is the internal name used when accessing.<br>
You can also set the display order of the categories here.<br>

### ファイル -File
記事の投稿、編集画面で登録したアイキャッチ画像、記事内の画像が確認できます。<br>

**<br>
You can check the posted images, the featured images registered on the edit screen, and the images in the article.<br>

#### 一覧 -List of files
![](/images/file-sample.png)

管理者はすべての画像の編集と削除ができます。一般ユーザーは自分で投稿した画像のみ編集できます。<br>

**<br>
The administrator can edit and delete all images. General users can only edit images posted by themselves.<br>

#### ファイルの編集 -Posting and editing files
![](/images/file-edit-sample.png)

編集画面では画像の説明文のみ編集できます。画像そのものを変更したり登録車を変更することはできません。<br>

**<br>
Only the description of the image can be edited on the edit screen. You cannot change the image itself or change the registrant.<br>

### 公開画面 -Public screen
![](/images/top-sample.png)

投稿した記事は一般公開されている画面で確認できるようになります。<br>
ステータスが公開中の記事のみ表示されます。<br>
投稿した記事ページ、カテゴリー別、年月別の一覧、404ページがデフォルトで用意されています。<br>
色や配置、内容などはお好みで変更して使ってくださいね。<br>

**<br>
You will be able to check the posted articles on the screen that is open to the public.<br>
Only articles with published status are displayed.<br>
By default, the posted article page, category, year / month list, and 404 page are prepared.<br>
Please change the color, arrangement, contents, etc. to your liking.<br>

### その他 -Others
梅子はまだまだ改善の余地があります。ちょっと物足りないかな、と思われることも多いかと思います。<br>
今後も導入したい機能は入れていく予定です。<br>
また、英語表記はGoogle翻訳を使用しています。<br>
製作者はあまり英語が得意ではありません（※読むことはできます）。<br>

**<br>
Umeko still has room for improvement. I think there are many people who think that it is a little unsatisfactory.<br>
We plan to add functions that we would like to introduce in the future.<br>
Also, the English notation uses Google Translate.<br>
The Umeko's creator is not very good at English (* I can read it).<br>

## 主な使用ライブラリ -Main used libraries
- <a href="https://laravel.com" target="_blank">Laravel 8</a>
- <a href="https://github.com/mewebstudio/captcha" target="_blank">Captcha for Laravel 5/6/7(mewebstudio)</a>
- <a href="https://alex-d.github.io/Trumbowyg/">Trumbowyg</a>

## 梅子CMS利用サイト -Umeko CMS usage site
- <a href="https://umeko.engineer-lady.com/" target="_blank">梅子CMS公式サイト -Umeko CMS Official Website</a>
- <a href="https://telework.engineer-lady.com/" target="_blank">快適！テレワーク生活 -Comfortable! Telework life</a>

Thank you!

## 制作者について -About the creator
通称「エンジニア婦人」。Naho Osadaです。<br>
PHPを得意としています。日本の個人事業主です。<br>
梅子の他、<a href="https://engineer-lady.com/" target="_blank">エンジニア婦人ノート</a>の運営、保守をしています。<br>

**<br>
Known as "engineer lady".My name is Naho Osada.<br>
I am good at PHP. I am a Japanese sole proprietor.<br>
In addition to 'Umeko', we also operate and maintain <a href="https://engineer-lady.com/" target="_blank">'engineer lady notes'</a>.<br>

## License
梅子-Umeko-は[MIT license](https://opensource.org/licenses/MIT)です。

**<br>
Umeko is [MIT license].<br>

Thank you for reading!!<br>
2021.12.25 Naho Osada
