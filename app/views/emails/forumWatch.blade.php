Hello, {{$user->username}}.<br><br>

A post has been made in the topic <a href="/forums/topic/{{$topic->id}}">{{$topic->title}}</a>. 
You are receiving this message because you have requested updates on this topic.
<br><br>
The post was made by {{$post->poster->username}}, and says:
<blockquote>
{{$post->body}}
</blockquote>
<br><br>
Thanks!<br>
The Carpe Noctem Mailer.