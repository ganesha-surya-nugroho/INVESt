<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa</title>
    <link rel="stylesheet" href="static/assets/css/dashboard_siswa.css">
    <!-- FONT -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Poppins:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
</head>
<body>
    <!-- NAVBAR -->
    
<nav>
        
    <a href="#" class="logo"><img src="static/assets/img/logo.png" alt=""><h2>ChatSchool</h2></a>

    <ul>
        <li><a href="dashboard_siswa.html"><img src="static/assets/img/dashboard.svg" alt="">Dashboard</a></li>
        <li><a href="siswa/kelas_siswa.php"><img src="static/assets/img/hat.svg" alt="">Kelas Saya</a></li>
        <li><a href="siswa/tugas_siswa.php"><img src="static/assets/img/tugas.svg" alt="">Tugas</a></li>
        <li><a href="siswa/materi_siswa.php"><img src="static/assets/img/materi.svg" alt="">Materi Pembelajaran</a></li>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle"><img src="static/assets/img/ai-tools.svg" alt="">AI Tools</a>
            <ul class="dropdown-menu">
            <li><a href="{{ url_for('index')}}">Flashcard Generator</a></li>
                <li><a href="{{ url_for('home')}}">PPT Generator</a></li>
                <li><a href="{{ url_for('summ')}}">AI Summarizer</a></li>
                <li><a href="{{ url_for('chat')}}">AI Chatbot</a></li>
                <li><a href="{{ url_for('coba') }}">Quiz Generator</a></li>
            </ul>
        </li>
    </ul>
    
    <a href="#" class="logout">Logout</a>
    
</nav>

    <!-- MAIN -->
     <main>
        <div class="hello">
            <h2>Hello, [PHP]</h2>
            <p>How are you today?</p>
        </div>

        <div class="ai-tools">

                <a href="#" class="ai-card">
                    <img src="static/assets/img/ai-ppt.png" alt="">
                    <p>AI PPT Generator</p>
                </a>

                <a href="#" class="ai-card">
                    <img src="static/assets/img/ai-flash.png" alt="">
                    <p>AI Flashcard Generator</p>
                </a>

                <a href="#" class="ai-card">
                    <img src="static/assets/img/ai-quiz.png" alt="">
                    <p>AI Quiz Generator</p>
                </a>

                <a href="#" class="ai-card">
                    <img src="static/assets/img/ai-summ1.png" alt="">
                    <p>AI Summarizer</p>
                </a>

                <a href="#" class="ai-card">
                    <img src="static/assets/img/chatbot.png" alt="">
                    <p>AI Study Asisstant</p>
                </a>
        </div>
     </main>
</body>
</html>