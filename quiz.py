from flask import Flask, request, render_template, session, redirect, url_for
from mistralai import Mistral

app = Flask(__name__)
app.secret_key = "supersecretkey"  # Untuk menyimpan session

# Masukkan API Key Mistral
api_key = "YHWHJycK5Lyelo3TSrHWY0em89ja9cEx"
client = Mistral(api_key=api_key)

# ID Agen
agent_id = "ag:ffcab88e:20241213:quiz:f14fd143"

# Fungsi untuk generate quiz
def generate_quiz(context):
    response = client.agents.complete(
        agent_id=agent_id,
        messages=[{
            "role": "user",
            "content": f"""Buatkan 5 pertanyaan kuis pilihan ganda berbasis konteks berikut:
[Konteks]

Setiap pertanyaan harus diformat sebagai berikut:
1. [Pertanyaan]
- [Pilihan A]
- [Pilihan B]
- [Pilihan C]
- [Pilihan D]
Correct Answer: [Jawaban benar (teks jawaban)]
{context}""",
        }],
    )
    quiz_text = response.choices[0].message.content
    return quiz_text

# Fungsi untuk mem-parsing kuis dari teks ke format Python
def parse_quiz(quiz_text):
    questions = quiz_text.strip().split("\n\n")
    quiz = []
    for question_block in questions:
        lines = question_block.split("\n")
        question = lines[0]
        options = [line.strip("- ") for line in lines[1:5]]
        correct_answer = lines[5].split(": ")[1].strip() if len(lines) > 5 else "Unknown"
        quiz.append({
            "question": question,
            "options": options,
            "correct_answer": correct_answer,
        })
    return quiz

# Halaman utama (form input)
@app.route("/")
def coba():
    return render_template("index-quiz.html")

# Endpoint untuk generate quiz
@app.route("/generate-quiz", methods=["POST"])
def handle_generate_quiz():
    context = request.form.get("context", "")
    if not context:
        return "Context is required", 400

    # Generate quiz
    quiz_text = generate_quiz(context)
    quiz = parse_quiz(quiz_text)

    # Simpan kuis di session
    session["quiz"] = quiz
    session["current_question"] = 0
    session["score"] = 0

    # Redirect ke halaman pertanyaan pertama
    return redirect(url_for("next_question"))

# Endpoint untuk menampilkan pertanyaan berikutnya
@app.route("/next-question", methods=["GET", "POST"])
def next_question():
    quiz = session.get("quiz", [])
    current_question = session.get("current_question", 0)
    score = session.get("score", 0)

    # Jika ini POST, periksa jawaban pengguna
    if request.method == "POST":
        user_answer = request.form.get("user_answer", "").strip().lower()
        correct_answer = quiz[current_question]["correct_answer"].strip().lower()

        if user_answer == correct_answer:
            score += 1
        session["score"] = score
        current_question += 1
        session["current_question"] = current_question

    # Jika masih ada pertanyaan, tampilkan
    if current_question < len(quiz):
        return render_template(
            "quiz-question.html",
            question=quiz[current_question],
            question_number=current_question + 1,
            total=len(quiz)
        )
    else:
        # Jika sudah selesai, tampilkan hasil
        return redirect(url_for("show_result"))

# Endpoint untuk menampilkan hasil kuis
@app.route("/result")
def show_result():
    score = session.get("score", 0)
    quiz = session.get("quiz", [])
    return render_template(
        "quiz-result.html",
        score=score,
        total=len(quiz),
        quiz=quiz
    )

if __name__ == "__main__":
    app.run(debug=True)
