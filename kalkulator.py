import tkinter as tk
from tkinter import messagebox

OPERASI = {
    "Tambah": lambda a, b: a + b,
    "Kurang": lambda a, b: a - b,
    "Kali": lambda a, b: a * b,
    "Bagi": lambda a, b: a / b,
}

SIMBOL = {"Tambah": "+", "Kurang": "-", "Kali": "x", "Bagi": ":"}


class KalkulatorApp(tk.Tk):
    def __init__(self):
        super().__init__()
        self.title("Kalkulator Matematika")
        self.geometry("360x320")
        self.resizable(False, False)

        container = tk.Frame(self)
        container.pack(fill="both", expand=True)
        self.container = container

        self.frames = {}
        for F in (MenuFrame, InputFrame):
            frame = F(container, self)
            self.frames[F] = frame
            frame.place(relwidth=1, relheight=1)

        self.show_frame(MenuFrame)

    def show_frame(self, frame_class):
        self.frames[frame_class].tkraise()

    def buka_input(self, operasi):
        self.frames[InputFrame].set_operasi(operasi)
        self.show_frame(InputFrame)


class MenuFrame(tk.Frame):
    def __init__(self, parent, controller):
        super().__init__(parent)
        tk.Label(self, text="Kalkulator Matematika", font=("Arial", 16, "bold")).pack(pady=20)
        tk.Label(self, text="Pilih Operasi", font=("Arial", 12)).pack(pady=5)

        for operasi in OPERASI:
            tk.Button(
                self,
                text=operasi,
                font=("Arial", 12),
                width=20,
                command=lambda op=operasi: controller.buka_input(op),
            ).pack(pady=8)


class InputFrame(tk.Frame):
    def __init__(self, parent, controller):
        super().__init__(parent)
        self.controller = controller
        self.operasi = None

        self.judul = tk.Label(self, text="", font=("Arial", 16, "bold"))
        self.judul.pack(pady=15)

        tk.Label(self, text="Angka 1").pack()
        self.entry1 = tk.Entry(self, font=("Arial", 12), justify="center")
        self.entry1.pack(pady=5)

        tk.Label(self, text="Angka 2").pack()
        self.entry2 = tk.Entry(self, font=("Arial", 12), justify="center")
        self.entry2.pack(pady=5)

        tk.Button(self, text="Hitung", font=("Arial", 12), command=self.hitung).pack(pady=15)

        self.hasil_label = tk.Label(self, text="", font=("Arial", 14, "bold"), fg="blue")
        self.hasil_label.pack(pady=5)

        tk.Button(self, text="Kembali ke Menu", command=self.kembali).pack(pady=10)

    def set_operasi(self, operasi):
        self.operasi = operasi
        self.judul.config(text=f"Operasi: {operasi}")
        self.entry1.delete(0, tk.END)
        self.entry2.delete(0, tk.END)
        self.hasil_label.config(text="")

    def hitung(self):
        try:
            angka1 = float(self.entry1.get())
            angka2 = float(self.entry2.get())
        except ValueError:
            messagebox.showerror("Error", "Masukkan angka yang valid")
            return

        if self.operasi == "Bagi" and angka2 == 0:
            messagebox.showerror("Error", "Tidak bisa membagi dengan nol")
            return

        hasil = OPERASI[self.operasi](angka1, angka2)
        simbol = SIMBOL[self.operasi]
        self.hasil_label.config(text=f"Hasil: {angka1} {simbol} {angka2} = {hasil}")

    def kembali(self):
        self.controller.show_frame(MenuFrame)


if __name__ == "__main__":
    app = KalkulatorApp()
    app.mainloop()
