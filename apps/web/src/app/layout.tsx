import Providers from "./providers";
import { ConnectionRecovery } from "@/components/connection-recovery";
import "./globals.css";

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body className="antialiased">
        <Providers>
          {children}
          <ConnectionRecovery />
        </Providers>
      </body>
    </html>
  );
}
