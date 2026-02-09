import Link from "next/link";

export default function Home() {
  return (
    <div className="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
      <main className="container mx-auto px-4 py-16">
        <div className="text-center">
          <h1 className="text-4xl font-bold text-gray-900 dark:text-white mb-4">
            Affiliate Product Showcase
          </h1>
          <p className="text-xl text-gray-600 dark:text-gray-300 mb-8">
            Enterprise-grade affiliate marketing platform
          </p>

          <div className="flex justify-center gap-4">
            <Link
              href="/products"
              className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              Browse Products
            </Link>
            <Link
              href="/admin"
              className="px-6 py-3 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors"
            >
              Admin Dashboard
            </Link>
          </div>
        </div>

        <div className="mt-16 grid md:grid-cols-3 gap-8">
          <FeatureCard
            title="Product Management"
            description="Manage affiliate products, categories, and inventory"
            icon="📦"
          />
          <FeatureCard
            title="Analytics"
            description="Track views, clicks, and conversions in real-time"
            icon="📊"
          />
          <FeatureCard
            title="Secure API"
            description="Enterprise-grade authentication and authorization"
            icon="🔒"
          />
        </div>

        <div className="mt-16 text-center">
          <p className="text-sm text-gray-500 dark:text-gray-400">
            API Status:{" "}
            <span className="text-green-500 font-medium">Operational</span> |
            Version: 1.0.0
          </p>
        </div>
      </main>
    </div>
  );
}

function FeatureCard({
  title,
  description,
  icon,
}: {
  title: string;
  description: string;
  icon: string;
}) {
  return (
    <div className="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
      <div className="text-3xl mb-4">{icon}</div>
      <h3 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
        {title}
      </h3>
      <p className="text-gray-600 dark:text-gray-300">{description}</p>
    </div>
  );
}
