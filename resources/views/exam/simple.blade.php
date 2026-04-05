<!DOCTYPE html>
<html>
<head>
    <title>Simple Exam</title>
</head>
<body>
    <h1>Exam Page Working!</h1>
    <p>Token: {{ $token }}</p>
    <p>Candidate: {{ $assignment->candidate->name }}</p>
    <p>Assessment: {{ $assignment->assessment->title }}</p>
    <a href="/exam/{{ $token }}/take">Start Exam</a>
</body>
</html>
