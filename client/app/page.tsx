"use client";

import { useEffect } from "react";

export default function Home() {
  useEffect(() => {
    const fethcer = async () => {
      console.log("reset");
      try {
        const res = await fetch("/api");
        if (!res.ok) {
          return;
        }
        const data = await res.json();
        console.log(data);
      } catch (err) {
        console.error(err);
      }
    };

    fethcer();
  }, []);

  return <h1>hellodsaod</h1>;
}
