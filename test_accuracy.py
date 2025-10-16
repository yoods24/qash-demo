import os
import cv2
import pickle
import numpy as np
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.neighbors import KNeighborsClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score, classification_report, confusion_matrix
from insightface.app import FaceAnalysis

# ========== Inisialisasi InsightFace ==========
app = FaceAnalysis(providers=['CPUExecutionProvider'])
app.prepare(ctx_id=0)

# ========== Path dataset ==========
dataset_path = "Dataset"
embeddings = []
labels = []

print("🔄 Membaca dan memproses wajah dari dataset...\n")

for person_name in os.listdir(dataset_path):
    person_folder = os.path.join(dataset_path, person_name)
    if not os.path.isdir(person_folder):
        continue

    for filename in os.listdir(person_folder):
        file_path = os.path.join(person_folder, filename)
        img = cv2.imread(file_path)
        if img is None:
            continue

        faces = app.get(img)
        if faces:
            embeddings.append(faces[0].embedding)
            labels.append(person_name)

# Konversi ke numpy array
embeddings = np.asarray(embeddings)
labels = np.asarray(labels)

if len(embeddings) == 0:
    print("❌ Tidak ada data wajah terdeteksi. Pastikan dataset ada.")
    exit()

# ========== Split Dataset (80% Train, 20% Test) ==========
X_train, X_test, y_train, y_test = train_test_split(
    embeddings, labels, test_size=0.2, random_state=42, stratify=labels
)

# ========== Latih KNN ==========
knn = KNeighborsClassifier(n_neighbors=3, metric='euclidean')
knn.fit(X_train, y_train)

# ========== Prediksi ==========
y_pred = knn.predict(X_test)

# ========== Evaluasi ==========
print("\n🎯 Akurasi:", accuracy_score(y_test, y_pred))
print("\n📊 Report Klasifikasi:\n", classification_report(y_test, y_pred))

# ========== Confusion Matrix ==========
cm = confusion_matrix(y_test, y_pred, labels=np.unique(labels))

plt.figure(figsize=(8,6))
sns.heatmap(cm, annot=True, fmt="d", cmap="Blues",
            xticklabels=np.unique(labels),
            yticklabels=np.unique(labels))
plt.xlabel("Predicted")
plt.ylabel("Actual")
plt.title("Confusion Matrix")
plt.show()
