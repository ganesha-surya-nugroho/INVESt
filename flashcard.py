from flask import Flask, request, jsonify, render_template
import logging
from mistralai import Mistral

# Konfigurasi aplikasi Flask
app = Flask(__name__)

# Konfigurasi logging
logging.basicConfig(level=logging.DEBUG)

# Inisialisasi API Mistral
api_key = "YHWHJycK5Lyelo3TSrHWY0em89ja9cEx"  # Gantilah dengan API key yang benar
client = Mistral(api_key=api_key)

# Fungsi untuk memanggil API Mistral
def call_mistral_api(topic):
    messages = [
        {
            "role": "user",
            "content": f"""
                Buatkan flashcards tentang "{topic}".Setiap Flashcard harus diformat sebagai berikut:

                Flashcard 1:
                - Front: [Front content of the flashcard]
                - Back: [Back content of the flashcard]

                Flashcard 2:
                - Front: [Front content of the flashcard]
                - Back: [Back content of the flashcard]

                Flashcard 3:
                - Front: [Front content of the flashcard]
                - Back: [Back content of the flashcard]

                Tolong Buatkan setidaknya 5 flashcard mengikuti format diatas.
                """
        }
    ]
    return client.agents.complete(
        agent_id="ag:ffcab88e:20241213:flashcard:0a3205ff",  # Gantilah dengan agent_id yang sesuai
        messages=messages,
    )

# Route utama untuk UI
@app.route('/')
def index():
    return render_template('flashcard.html')

# Route untuk generate flashcard
@app.route('/generate-flashcard', methods=['POST'])
def generate_flashcard():
    data = request.json
    logging.debug(f"Received request data: {data}")

    topic = data.get('topic', '').strip()
    if not topic:
        logging.error("Topic is missing in request")
        return jsonify({"error": "Topic is required"}), 400

    try:
        logging.debug(f"Calling Mistral API with topic: {topic}")
        chat_response = call_mistral_api(topic)
        flashcards_raw = chat_response.choices[0].message.content.strip()

        # Parsing flashcards dari format yang terstruktur
        flashcards = []
        for card in flashcards_raw.split("\n**Flashcard")[1:]:
            parts = card.split("- Front:")
            if len(parts) > 1:
                front_back = parts[1].split("- Back:")
                front = front_back[0].strip()
                back = front_back[1].strip() if len(front_back) > 1 else "No content"
                flashcards.append({"front": front, "back": back})

        logging.debug(f"Parsed flashcards: {flashcards}")
        return jsonify({"flashcards": flashcards})

    except Exception as e:
        logging.error(f"Error occurred: {e}", exc_info=True)

        # Fallback: mock data
        fallback_flashcards = [
            {
                "front": f"Apa itu {topic}?",
                "back": f"{topic} ",
            }
        ]
        return jsonify({"flashcards": fallback_flashcards}), 200

# Main execution
if __name__ == '__main__':
    app.run(debug=True)
